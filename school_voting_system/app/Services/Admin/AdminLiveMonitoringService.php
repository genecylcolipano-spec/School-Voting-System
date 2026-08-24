<?php

namespace App\Services\Admin;

use App\Enums\ElectionStatus;
use App\Enums\TalentJudgeScoreStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventJudge;
use App\Models\TalentEventVote;
use App\Models\TalentJudgeScoreSheet;
use App\Models\User;
use App\Models\Vote;
use App\Services\Talent\TalentResultsRankingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Super Admin / Admin Live Monitoring snapshots from real Elections & Talent Competitions.
 */
class AdminLiveMonitoringService
{
    public function __construct(
        protected AdminScopeService $scope,
        protected TalentResultsRankingService $talentRankingService,
    ) {}

    /**
     * @param  array{
     *     administrator?: ?int,
     *     school_year?: ?string,
     *     status?: ?string,
     *     active_only?: bool,
     *     published?: bool,
     *     results_pending?: bool
     * }  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function electionCards(User $viewer, array $filters = []): Collection
    {
        $query = Election::query()
            ->with(['creator:id,name,account_id'])
            ->withCount(['candidates', 'votes'])
            ->orderByDesc('voting_starts_at');

        $this->scopeElectionsQuery($query, $viewer);

        if (! empty($filters['administrator'])) {
            $query->where('created_by', (int) $filters['administrator']);
        }

        $elections = $query->get();
        $ids = $elections->pluck('id');

        $lastVotes = Vote::query()
            ->selectRaw('election_id, MAX(voted_at) as last_vote_at')
            ->whereIn('election_id', $ids)
            ->groupBy('election_id')
            ->pluck('last_vote_at', 'election_id');

        $uniqueVoters = Vote::query()
            ->selectRaw('election_id, COUNT(DISTINCT user_id) as unique_voters')
            ->whereIn('election_id', $ids)
            ->groupBy('election_id')
            ->pluck('unique_voters', 'election_id');

        $liveElectionIds = $elections
            ->filter(fn (Election $election) => in_array($this->electionPhase($election)['key'], ['voting_open', 'voting_paused'], true))
            ->pluck('id');

        $positionLeaders = $this->batchElectionPositionLeaders($liveElectionIds);

        $cards = $elections->map(function (Election $election) use ($lastVotes, $uniqueVoters, $viewer, $positionLeaders) {
            return $this->mapElection(
                $election,
                $lastVotes[$election->id] ?? null,
                (int) ($uniqueVoters[$election->id] ?? 0),
                $viewer,
                $positionLeaders->get($election->id, []),
            );
        });

        return $this->sortByUrgency($this->applyCommonFilters($cards, $filters))->values();
    }

    /**
     * @param  array{
     *     administrator?: ?int,
     *     school_year?: ?string,
     *     status?: ?string,
     *     active_only?: bool,
     *     published?: bool,
     *     results_pending?: bool
     * }  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function talentCards(User $viewer, array $filters = []): Collection
    {
        $query = TalentEvent::query()
            ->with(['creator:id,name,account_id'])
            ->withCount([
                'votes',
                'entries',
                'judges',
                'entries as approved_entries_count' => fn ($q) => $q->where('status', TalentEventEntry::STATUS_APPROVED),
            ])
            ->orderByDesc('event_date');

        $this->scopeTalentQuery($query, $viewer);

        if (! empty($filters['administrator'])) {
            $query->where('created_by', (int) $filters['administrator']);
        }

        $events = $query->get();
        $ids = $events->pluck('id');

        $lastVotes = TalentEventVote::query()
            ->selectRaw('talent_event_id, MAX(COALESCE(voted_at, created_at)) as last_vote_at')
            ->whereIn('talent_event_id', $ids)
            ->groupBy('talent_event_id')
            ->pluck('last_vote_at', 'talent_event_id');

        $submittedByJudge = TalentJudgeScoreSheet::query()
            ->whereIn('talent_event_id', $ids)
            ->where('status', TalentJudgeScoreStatus::Submitted)
            ->get(['talent_event_id', 'user_id'])
            ->groupBy('talent_event_id')
            ->map(fn (Collection $rows) => $rows->pluck('user_id')->unique()->values());

        $activeJudges = TalentEventJudge::query()
            ->active()
            ->whereIn('talent_event_id', $ids)
            ->get(['talent_event_id', 'user_id'])
            ->groupBy('talent_event_id')
            ->map(fn (Collection $rows) => $rows->pluck('user_id')->unique()->values());

        $cards = $events->map(function (TalentEvent $event) use ($lastVotes, $submittedByJudge, $activeJudges) {
            return $this->mapTalent(
                $event,
                $lastVotes[$event->id] ?? null,
                $submittedByJudge->get($event->id, collect()),
                $activeJudges->get($event->id, collect()),
            );
        });

        return $this->sortByUrgency($this->applyCommonFilters($cards, $filters))->values();
    }

    /**
     * @return array{generated_at: string, cards: list<array<string, mixed>>, summary: array<string, int|float>}
     */
    public function electionPoll(User $viewer, array $filters = []): array
    {
        $cards = $this->electionCards($viewer, $filters);

        return [
            'generated_at' => now()->toIso8601String(),
            'cards' => $cards->all(),
            'summary' => $this->summarize($cards, 'election'),
        ];
    }

    /**
     * @return array{generated_at: string, cards: list<array<string, mixed>>, summary: array<string, int|float>}
     */
    public function talentPoll(User $viewer, array $filters = []): array
    {
        $cards = $this->talentCards($viewer, $filters);

        return [
            'generated_at' => now()->toIso8601String(),
            'cards' => $cards->all(),
            'summary' => $this->summarize($cards, 'talent'),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $cards
     * @return array<string, int|float>
     */
    public function summarize(Collection $cards, string $mode): array
    {
        $live = $cards->where('is_live', true)->count();
        $paused = $cards->where('status_key', 'voting_paused')->count();
        $pending = $cards->where('is_results_pending', true)->count();
        $published = $cards->where('is_published', true)->count();
        $votes = (int) $cards->sum('votes_cast');
        $owners = $cards->pluck('owner_id')->filter()->unique()->count();

        return [
            'live_now' => $live,
            'voting_open' => $live + $paused,
            'results_pending' => $pending,
            'published' => $published,
            'total_votes' => $votes,
            'active_owners' => $owners,
            'total_activities' => $cards->count(),
            'mode' => $mode,
        ];
    }

    /**
     * @return Collection<int, User>
     */
    public function administratorOptions(User $viewer): Collection
    {
        if (! $viewer->isSuperAdmin()) {
            return collect([$viewer]);
        }

        return User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'account_id', 'role']);
    }

    /**
     * @return list<int>
     */
    public function schoolYearOptions(User $viewer): array
    {
        $electionYears = Election::query()
            ->tap(fn ($q) => $this->scopeElectionsQuery($q, $viewer))
            ->get(['voting_starts_at', 'created_at'])
            ->map(fn (Election $e) => ($e->voting_starts_at ?? $e->created_at)?->year)
            ->filter()
            ->toBase();

        $talentYears = TalentEvent::query()
            ->tap(fn ($q) => $this->scopeTalentQuery($q, $viewer))
            ->get(['event_date', 'created_at'])
            ->map(fn (TalentEvent $e) => ($e->event_date ?? $e->created_at)?->year)
            ->filter()
            ->toBase();

        return $electionYears
            ->merge($talentYears)
            ->unique()
            ->sortDesc()
            ->values()
            ->map(fn ($y) => (int) $y)
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $positionLeaders
     * @return array<string, mixed>
     */
    protected function mapElection(
        Election $election,
        mixed $lastVoteAt,
        int $uniqueVoters,
        User $viewer,
        array $positionLeaders = [],
    ): array {
        $phase = $this->electionPhase($election);
        $isLive = $phase['key'] === 'voting_open';
        $showPositionLeaders = in_array($phase['key'], ['voting_open', 'voting_paused'], true);
        $eligible = $election->eligibleVoterCount();
        $votesCast = (int) ($election->votes_count ?? 0);
        $turnout = $eligible > 0 ? round(($uniqueVoters / $eligible) * 100, 1) : 0.0;
        $lastAt = $lastVoteAt ? Carbon::parse($lastVoteAt) : null;
        $inScope = $viewer->isSuperAdmin()
            || $this->scope->assignedElection($viewer)?->id === $election->id;
        $canManageLive = $this->scope->canPauseElection($viewer)
            && $inScope
            && $showPositionLeaders;

        $schedule = collect([
            $election->voting_starts_at?->format('M d, Y g:i A'),
            $election->voting_ends_at?->format('M d, Y g:i A'),
        ])->filter()->implode(' → ') ?: 'Schedule TBA';

        return [
            'type' => 'election',
            'id' => $election->id,
            'slug' => $election->slug,
            'name' => $election->title,
            'banner_url' => null,
            'has_banner' => false,
            'owner_id' => $election->created_by,
            'owner_name' => $election->creator?->name ?? 'System',
            'owner_account' => $election->creator?->account_id,
            'status_key' => $phase['key'],
            'status_label' => $phase['label'],
            'phase' => $phase['label'],
            'phase_steps' => $this->electionPhaseSteps($phase['key']),
            'is_live' => $isLive,
            'is_urgent' => $isLive || $phase['key'] === 'voting_paused',
            'is_published' => (bool) $election->public_results_published,
            'is_results_pending' => $phase['key'] === 'results_pending',
            'schedule' => $schedule,
            'registered_voters' => $eligible,
            'votes_cast' => $votesCast,
            'turnout_percent' => $turnout,
            'candidates_count' => (int) ($election->candidates_count ?? 0),
            'last_vote_at' => $lastAt?->diffForHumans() ?? '—',
            'last_vote_at_iso' => $lastAt?->toIso8601String(),
            'countdown' => $this->compactCountdown($election->countdownSnapshot()),
            'show_position_leaders' => $showPositionLeaders,
            'position_leaders' => $showPositionLeaders ? $positionLeaders : [],
            'school_year' => ($election->voting_starts_at ?? $election->created_at)?->year,
            'details_url' => route('admin.elections.edit', $election),
            'results_url' => route('admin.results.election.show', $election),
            'show_results_shortcut' => $phase['key'] === 'published',
            'can_manage_live' => $canManageLive,
            'is_paused' => (bool) $election->is_paused,
            'actions' => [
                'pause' => route('admin.election.pause', $election),
                'resume' => route('admin.election.resume', $election),
            ],
            'freeze_totals' => in_array($phase['key'], ['voting_closed', 'results_pending', 'published'], true),
            'urgency_rank' => $this->urgencyRank($phase['key']),
        ];
    }

    /**
     * @param  Collection<int, int|string>  $completedJudgeIds
     * @param  Collection<int, int|string>  $activeJudgeIds
     * @return array<string, mixed>
     */
    protected function mapTalent(
        TalentEvent $event,
        mixed $lastVoteAt,
        Collection $completedJudgeIds,
        Collection $activeJudgeIds,
    ): array {
        $phase = $this->talentPhase($event);
        $isLive = $phase['key'] === 'voting_open';
        $judgesTotal = (int) ($event->judges_count ?? $activeJudgeIds->count());
        $judgesCompleted = $activeJudgeIds->intersect($completedJudgeIds)->count();
        $lastAt = $lastVoteAt ? Carbon::parse($lastVoteAt) : null;

        $rankings = $isLive ? $this->talentLeaderboard($event) : [];

        return [
            'type' => 'talent',
            'id' => $event->id,
            'slug' => $event->slug,
            'name' => $event->title,
            'banner_url' => $event->hasLandscapeCompetitionBanner() ? $event->image_url : null,
            'has_banner' => $event->hasLandscapeCompetitionBanner(),
            'owner_id' => $event->created_by,
            'owner_name' => $event->creator?->name ?? 'System',
            'owner_account' => $event->creator?->account_id,
            'category' => $event->talent_category?->label() ?? ($event->type?->label() ?? 'Talent Competition'),
            'status_key' => $phase['key'],
            'status_label' => $phase['label'],
            'phase' => $phase['label'],
            'phase_steps' => $this->talentPhaseSteps($phase['key']),
            'is_live' => $isLive,
            'is_urgent' => $isLive || $phase['key'] === 'voting_paused',
            'is_published' => $phase['key'] === 'published',
            'is_results_pending' => in_array($phase['key'], ['results_pending', 'voting_closed'], true),
            'registration_count' => (int) ($event->entries_count ?? 0),
            'approved_participants' => (int) ($event->approved_entries_count ?? 0),
            'votes_cast' => (int) ($event->votes_count ?? 0),
            'judges_completed' => $judgesCompleted,
            'judges_remaining' => max(0, $judgesTotal - $judgesCompleted),
            'judges_total' => $judgesTotal,
            'last_vote_at' => $lastAt?->diffForHumans() ?? '—',
            'last_vote_at_iso' => $lastAt?->toIso8601String(),
            'countdown' => $this->compactCountdown($event->countdownSnapshot()),
            'school_year' => ($event->event_date ?? $event->created_at)?->year,
            'rankings' => $rankings,
            'details_url' => route('admin.talent-competition.show', $event),
            'results_url' => route('admin.results.talent.show', $event),
            'show_results_shortcut' => $phase['key'] === 'published',
            'actions' => [
                'pause' => route('admin.live.talent.pause', $event),
                'resume' => route('admin.live.talent.resume', $event),
                'close' => route('admin.live.talent.close', $event),
                'export' => route('admin.live.talent.export', $event),
                'participants' => route('admin.talent-participants.index', ['event' => $event->id]),
                'competition' => route('admin.talent-competition.show', $event),
                'results' => route('admin.results.talent.show', $event),
            ],
            'is_paused' => (bool) $event->is_paused,
            'can_manage_live' => $isLive || $phase['key'] === 'voting_paused',
            'freeze_totals' => in_array($phase['key'], ['voting_closed', 'results_pending', 'published'], true),
            'urgency_rank' => $this->urgencyRank($phase['key']),
        ];
    }

    /**
     * @param  Collection<int, int|string>  $electionIds
     * @return Collection<int|string, list<array<string, mixed>>>
     */
    protected function batchElectionPositionLeaders(Collection $electionIds): Collection
    {
        if ($electionIds->isEmpty()) {
            return collect();
        }

        $candidates = Candidate::query()
            ->whereIn('election_id', $electionIds)
            ->where('is_active', true)
            ->with(['category:id,name,sort_order'])
            ->withCount('votes')
            ->get();

        return $candidates
            ->groupBy('election_id')
            ->map(fn (Collection $group) => $this->positionLeadersFromCandidates($group));
    }

    /**
     * @param  Collection<int, Candidate>  $candidates
     * @return list<array{
     *     position: string,
     *     name: ?string,
     *     display: string,
     *     votes: int,
     *     percent: float,
     *     tied: bool,
     *     tie_count: int
     * }>
     */
    protected function positionLeadersFromCandidates(Collection $candidates): array
    {
        $grouped = $candidates->groupBy(fn (Candidate $candidate) => $candidate->election_category_id
            ?: 'position:'.strtolower(trim((string) ($candidate->position ?: 'position'))));

        $rows = $grouped->map(function (Collection $group) {
            $first = $group->first();
            $position = $first?->category?->name ?? $first?->position ?? 'Position';
            $total = (int) $group->sum('votes_count');
            $maxVotes = (int) $group->max('votes_count');

            $leaders = $group
                ->filter(fn (Candidate $candidate) => $maxVotes > 0 && (int) $candidate->votes_count === $maxVotes)
                ->sortBy(fn (Candidate $candidate) => strtolower((string) $candidate->display_name))
                ->values();

            $names = $leaders->pluck('display_name')->filter()->values();
            $tieCount = $names->count();
            $tied = $tieCount > 1;
            $leadName = $names->first();

            return [
                'position' => $position,
                'name' => $leadName,
                'display' => match (true) {
                    $tieCount === 0 => '—',
                    $tied => $leadName.' +'.($tieCount - 1),
                    default => (string) $leadName,
                },
                'votes' => $maxVotes,
                'percent' => $total > 0 ? round(($maxVotes / $total) * 100, 1) : 0.0,
                'tied' => $tied,
                'tie_count' => $tieCount,
                'sort_order' => (int) ($first?->category?->sort_order ?? 999),
                'position_rank' => $this->positionRank($position),
            ];
        });

        return $rows
            ->sortBy(fn (array $row) => [
                $row['position_rank'],
                $row['sort_order'],
                strtolower($row['position']),
            ])
            ->values()
            ->map(fn (array $row) => collect($row)->except(['sort_order', 'position_rank'])->all())
            ->all();
    }

    /**
     * @param  array{
     *     label?: string,
     *     remaining?: string,
     *     phase?: string,
     *     target_at_iso?: string,
     *     is_closed?: bool
     * }|null  $snapshot
     * @return array{label: string, remaining: string, phase: string, target_at_iso: string, is_closed: bool}|null
     */
    protected function compactCountdown(?array $snapshot): ?array
    {
        if (! $snapshot || empty($snapshot['target_at_iso'])) {
            return null;
        }

        $phase = (string) ($snapshot['phase'] ?? 'active');
        $isClosed = (bool) ($snapshot['is_closed'] ?? $phase === 'ended');

        if ($isClosed) {
            return null;
        }

        return [
            'label' => (string) ($snapshot['label'] ?? 'Time remaining'),
            'remaining' => (string) ($snapshot['remaining'] ?? '—'),
            'phase' => $phase,
            'target_at_iso' => (string) $snapshot['target_at_iso'],
            'is_closed' => false,
        ];
    }

    protected function positionRank(string $position): int
    {
        $normalized = strtolower(trim($position));

        if (preg_match('/\bvice[\s-]*president\b/', $normalized)) {
            return 2;
        }

        if (preg_match('/\bpresident\b/', $normalized)) {
            return 1;
        }

        if (str_contains($normalized, 'secretary')) {
            return 3;
        }

        if (str_contains($normalized, 'treasurer')) {
            return 4;
        }

        if (str_contains($normalized, 'auditor')) {
            return 5;
        }

        if (str_contains($normalized, 'pro')) {
            return 6;
        }

        return 100;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function talentLeaderboard(TalentEvent $event): array
    {
        return array_slice($this->talentRankingService->rankings($event), 0, 10);
    }

    /**
     * @return array{key: string, label: string}
     */
    protected function electionPhase(Election $election): array
    {
        if ($election->public_results_published) {
            return ['key' => 'published', 'label' => 'Published'];
        }

        if ($election->is_paused) {
            return ['key' => 'voting_paused', 'label' => 'Voting Paused'];
        }

        if ($election->isAcceptingVotes()) {
            return ['key' => 'voting_open', 'label' => 'Voting Open'];
        }

        if ($election->isAwaitingResultsPublication()) {
            return ['key' => 'results_pending', 'label' => 'Results Pending'];
        }

        if ($election->isAfterVotingEnd()
            || in_array($election->status, [ElectionStatus::Closed, ElectionStatus::Archived], true)) {
            return ['key' => 'voting_closed', 'label' => 'Voting Closed'];
        }

        return ['key' => 'scheduled', 'label' => 'Scheduled'];
    }

    /**
     * @return array{key: string, label: string}
     */
    protected function talentPhase(TalentEvent $event): array
    {
        $key = $event->currentStatusKey();

        $hasJudges = (int) ($event->judges_count ?? $event->judges()->count()) > 0;

        return match ($key) {
            'registration_open' => ['key' => 'registration_open', 'label' => 'Registration Open'],
            'registration_closed' => $hasJudges
                ? ['key' => 'judging_open', 'label' => 'Judging Open']
                : ['key' => 'registration_closed', 'label' => 'Registration Closed'],
            'voting_open' => ['key' => 'voting_open', 'label' => 'Voting Open'],
            'voting_paused' => ['key' => 'voting_paused', 'label' => 'Voting Paused'],
            'voting_closed' => ['key' => 'results_pending', 'label' => 'Results Pending'],
            'results_published' => ['key' => 'published', 'label' => 'Published'],
            'scheduled' => $hasJudges
                ? ['key' => 'judging_open', 'label' => 'Judging Open']
                : ['key' => 'scheduled', 'label' => 'Scheduled'],
            'archived' => ['key' => 'published', 'label' => 'Published'],
            default => ['key' => $key, 'label' => $event->displayStatusLabel()],
        };
    }

    protected function scopeElectionsQuery($query, User $viewer): void
    {
        if ($viewer->isSuperAdmin()) {
            return;
        }

        $assignedId = $this->scope->assignment($viewer)?->election_id;

        $query->where(function ($inner) use ($viewer, $assignedId) {
            $inner->where('created_by', $viewer->id);

            if ($assignedId) {
                $inner->orWhere('id', $assignedId);
            }
        });
    }

    protected function scopeTalentQuery($query, User $viewer): void
    {
        if ($viewer->isSuperAdmin()) {
            return;
        }

        $assignedId = $this->scope->assignment($viewer)?->election_id;

        $query->where(function ($inner) use ($viewer, $assignedId) {
            $inner->where('created_by', $viewer->id);

            if ($assignedId) {
                $inner->orWhere('election_id', $assignedId);
            }
        });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $cards
     * @return Collection<int, array<string, mixed>>
     */
    protected function applyCommonFilters(Collection $cards, array $filters): Collection
    {
        return $cards
            ->when(! empty($filters['school_year']), fn ($c) => $c->where('school_year', (int) $filters['school_year']))
            ->when(! empty($filters['status']), fn ($c) => $c->where('status_key', $filters['status']))
            ->when(! empty($filters['active_only']), fn ($c) => $c->filter(fn ($card) => in_array($card['status_key'] ?? '', [
                'voting_open',
                'registration_open',
                'judging_open',
                'voting_paused',
            ], true)))
            ->when(! empty($filters['published']), fn ($c) => $c->where('is_published', true))
            ->when(! empty($filters['results_pending']), fn ($c) => $c->where('is_results_pending', true))
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $cards
     * @return Collection<int, array<string, mixed>>
     */
    protected function sortByUrgency(Collection $cards): Collection
    {
        return $cards->sortBy([
            fn (array $card) => (int) ($card['urgency_rank'] ?? 99),
            fn (array $card) => -1 * (int) ($card['votes_cast'] ?? 0),
            fn (array $card) => strtolower((string) ($card['name'] ?? '')),
        ])->values();
    }

    protected function urgencyRank(string $statusKey): int
    {
        return match ($statusKey) {
            'voting_open' => 0,
            'voting_paused' => 1,
            'results_pending', 'voting_closed' => 2,
            'registration_open', 'judging_open' => 3,
            'registration_closed' => 4,
            'scheduled' => 5,
            'published' => 6,
            default => 7,
        };
    }

    /**
     * @return list<array{key: string, label: string, state: string}>
     */
    protected function electionPhaseSteps(string $current): array
    {
        $order = ['scheduled', 'voting_open', 'voting_closed', 'results_pending', 'published'];
        $labels = [
            'scheduled' => 'Scheduled',
            'voting_open' => 'Open',
            'voting_closed' => 'Closed',
            'results_pending' => 'Pending',
            'published' => 'Published',
        ];

        $normalized = match ($current) {
            'voting_paused' => 'voting_open',
            default => $current,
        };

        $currentIndex = array_search($normalized, $order, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return collect($order)->map(function (string $key, int $index) use ($labels, $currentIndex, $normalized) {
            $state = 'upcoming';
            if ($index < $currentIndex) {
                $state = 'done';
            } elseif ($index === $currentIndex) {
                $state = $normalized === 'voting_open' ? 'live' : 'current';
            }

            return [
                'key' => $key,
                'label' => $labels[$key],
                'state' => $state,
            ];
        })->all();
    }

    /**
     * @return list<array{key: string, label: string, state: string}>
     */
    protected function talentPhaseSteps(string $current): array
    {
        $order = ['registration_open', 'judging_open', 'voting_open', 'results_pending', 'published'];
        $labels = [
            'registration_open' => 'Registration',
            'judging_open' => 'Judging',
            'voting_open' => 'Voting',
            'results_pending' => 'Pending',
            'published' => 'Published',
        ];

        $normalized = match ($current) {
            'voting_paused' => 'voting_open',
            'registration_closed', 'scheduled' => 'judging_open',
            'voting_closed' => 'results_pending',
            default => $current,
        };

        $currentIndex = array_search($normalized, $order, true);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        return collect($order)->map(function (string $key, int $index) use ($labels, $currentIndex, $normalized) {
            $state = 'upcoming';
            if ($index < $currentIndex) {
                $state = 'done';
            } elseif ($index === $currentIndex) {
                $state = $normalized === 'voting_open' ? 'live' : 'current';
            }

            return [
                'key' => $key,
                'label' => $labels[$key],
                'state' => $state,
            ];
        })->all();
    }
}
