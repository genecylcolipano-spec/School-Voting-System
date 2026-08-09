<?php

namespace App\Services\Admin;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Models\AuditLog;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Models\Vote;
use App\Services\Election\ElectionIntegrityService;
use App\Services\Talent\StudentTalentService;
use App\Support\EventImageUrl;
use App\Support\SchoolBranding;
use App\Support\WinnerSpotlightBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminResultsService
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AdminLiveVotingService $liveVoting,
        protected StudentTalentService $talentService,
        protected ElectionResultsPublishingService $electionPublishing,
        protected ElectionIntegrityService $integrity,
    ) {}

    /**
     * @return Collection<int, array{key: string, label: string, type: string, id: int}>
     */
    public function filterOptions(User $admin): Collection
    {
        return $this->listEvents($admin)->map(fn (array $event) => [
            'key' => $event['key'],
            'label' => $event['name'],
            'type' => $event['type'],
            'id' => $event['id'],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listEvents(User $admin, ?string $filterKey = null): Collection
    {
        $events = collect();

        foreach ($this->visibleElections($admin) as $election) {
            $events->push($this->summarizeElection($election, $admin));
        }

        foreach ($this->visibleTalentEvents($admin) as $talentEvent) {
            $events->push($this->summarizeTalentEvent($talentEvent, $admin));
        }

        return $events
            ->when($filterKey, fn (Collection $collection) => $collection->where('key', $filterKey))
            ->sortByDesc(fn (array $event) => $event['sort_at'] ?? now()->toDateTimeString())
            ->values();
    }

    public function assertCanViewElection(User $admin, Election $election): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        $assignedId = $this->scope->assignment($admin)?->election_id;

        abort_unless(
            ((int) $assignedId === (int) $election->id)
            || ((int) $election->created_by === (int) $admin->id),
            403,
        );
    }

    public function assertCanViewTalentEvent(User $admin, TalentEvent $talentEvent): void
    {
        if ($admin->isSuperAdmin()) {
            return;
        }

        // Match Live Monitoring scope: creator OR assigned election.
        if ((int) $talentEvent->created_by === (int) $admin->id) {
            return;
        }

        $assignedId = $this->scope->assignment($admin)?->election_id;

        if ($assignedId && (int) $talentEvent->election_id === (int) $assignedId) {
            return;
        }

        $election = $talentEvent->election()->withTrashed()->first();

        if ($election) {
            $this->assertCanViewElection($admin, $election);

            return;
        }

        abort(403);
    }

    /**
     * @return array<string, mixed>
     */
    public function electionDetail(Election $election, User $admin): array
    {
        $this->assertCanViewElection($admin, $election);
        $election->loadMissing('resultsPublisher');

        $live = $this->liveVoting->progress($admin);
        $rankings = $this->electionRankings($election);
        $winners = $this->electionWinners($rankings);
        $winnerSpotlight = WinnerSpotlightBuilder::fromRankings($rankings);
        $stats = $this->electionStats($election, $admin);
        $isLive = $this->isElectionLive($election);
        $isFinal = $this->isElectionFinal($election);
        $isPublished = $this->electionPublishing->isPublished($election);
        $isReadyForReview = $this->electionPublishing->isReadyForReview($election);
        $canPublish = $this->scope->canPublishElectionResults($admin) && $isReadyForReview;
        $canUnpublish = $this->scope->canUnpublishElectionResults($admin) && $isPublished;

        return [
            'type' => 'election',
            'key' => $this->eventKey('election', $election->id),
            'id' => $election->id,
            'slug' => $election->slug,
            'name' => $election->title,
            'category' => 'Student Election',
            'category_kind' => 'election',
            'description' => $election->description,
            'voting_status' => $this->electionVotingStatus($election),
            'voting_status_tone' => $this->electionStatusTone($election),
            'starts_at' => $election->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $election->voting_ends_at?->format('M d, Y g:i A'),
            'is_live' => $isLive,
            'is_final' => $isFinal,
            'is_published' => $isPublished,
            'is_ready_for_review' => $isReadyForReview || $isPublished,
            'can_publish' => $canPublish,
            'can_unpublish' => $canUnpublish,
            'publish_url' => route('admin.election.publish-results', $election),
            'unpublish_url' => route('admin.election.unpublish-results', $election),
            'results_published_at' => $election->results_published_at?->format('M d, Y g:i A'),
            'results_published_by' => $election->resultsPublisher?->name,
            'live_banner' => $isLive ? 'live' : ($isPublished ? 'published' : ($isFinal ? 'review' : 'idle')),
            'summary' => [
                'total_votes' => $stats['total_votes'],
                'turnout_percent' => $stats['turnout_percent'],
                'winners_count' => count($winners),
                'participants' => $stats['participants'],
            ],
            'winners' => $winners,
            'winner_spotlight' => $winnerSpotlight,
            'primary_winner' => WinnerSpotlightBuilder::primaryWinner($winnerSpotlight),
            'lifecycle_steps' => $this->electionLifecycleSteps($election),
            'winners_layout' => 'election',
            'rankings' => $rankings,
            'charts' => $this->electionCharts($rankings),
            'activity' => $this->electionActivity($election),
            'updated_at' => now()->toIso8601String(),
            'generated_at' => now()->format('M d, Y g:i A'),
            'can_export' => $this->scope->canExportPreliminaryResults($admin),
            'party_performance' => $this->partyPerformanceFromRankings($rankings, $winners),
            'turnout_sections' => $this->turnoutSectionsForExport($admin, $election),
            'integrity' => $this->integrity->verify($election),
            'verify_integrity_url' => route('admin.results.election.verify-integrity', $election),
            'turnout_export_url' => route('admin.results.election.turnout', $election),
            'live_payload' => $isLive && ($live['is_live'] ?? false) ? $live : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function talentDetail(TalentEvent $talentEvent, User $admin): array
    {
        $this->assertCanViewTalentEvent($admin, $talentEvent);

        $isLive = $this->isTalentLive($talentEvent);
        $isFinal = $this->isTalentFinal($talentEvent);
        $canViewVoteCounts = $this->scope->canViewTalentVoteCounts($admin, $talentEvent);

        if ($isLive && ! $canViewVoteCounts) {
            abort(403, 'Unauthorized to view live vote totals.');
        }

        $rankings = $this->talentRankings($talentEvent);
        $winners = $this->talentWinners($talentEvent, $rankings);
        $stats = $this->talentStats($talentEvent, $admin);
        $categoryKind = $this->talentCategoryKind($talentEvent);
        $timeline = $this->talentVoteTimeline($talentEvent);
        $charts = $this->talentCharts($rankings);
        $charts['hourly'] = $timeline['hourly'];
        $charts['daily'] = $timeline['daily'];

        return [
            'type' => 'talent',
            'key' => $this->eventKey('talent', $talentEvent->id),
            'id' => $talentEvent->id,
            'slug' => $talentEvent->slug,
            'name' => $talentEvent->title,
            'category' => $this->talentCategoryLabel($talentEvent),
            'category_kind' => $categoryKind,
            'description' => $talentEvent->description,
            'event_settings' => $this->talentEventSettings($talentEvent, $stats),
            'voting_status' => $talentEvent->displayStatusLabel(),
            'voting_status_tone' => $this->talentStatusTone($talentEvent),
            'starts_at' => $talentEvent->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $talentEvent->voting_ends_at?->format('M d, Y g:i A'),
            'ends_at_iso' => $talentEvent->voting_ends_at?->toIso8601String(),
            'is_live' => $isLive,
            'is_paused' => (bool) $talentEvent->is_paused,
            'is_final' => $isFinal,
            'live_banner' => $talentEvent->is_paused ? 'paused' : ($isLive ? 'live' : ($isFinal ? 'final' : 'idle')),
            'summary' => [
                'total_votes' => $stats['total_votes'],
                'turnout_percent' => $stats['turnout_percent'],
                'winners_count' => count(array_filter($winners, fn ($w) => ($w['name'] ?? '') !== '')),
                'participants' => $stats['participants'],
                'total_entries' => $stats['total_entries'],
                'pending_entries' => $stats['pending_entries'],
                'approved_entries' => $stats['approved_entries'],
                'rejected_entries' => $stats['rejected_entries'],
                'unique_voters' => $stats['unique_voters'],
                'eligible_voters' => $stats['eligible_voters'],
            ],
            'status_breakdown' => [
                'registration' => $talentEvent->isRegistrationOpen() ? 'Open' : ($talentEvent->registration_starts_at ? 'Closed' : 'Not scheduled'),
                'submission' => $talentEvent->submission_deadline && now()->gt($talentEvent->submission_deadline)
                    ? 'Closed'
                    : ($talentEvent->isRegistrationOpen() ? 'Open' : 'Closed'),
                'voting' => $talentEvent->displayStatusLabel(),
                'results' => $talentEvent->results_published_at ? 'Published' : 'Unpublished',
            ],
            'winners' => $winners,
            'winners_layout' => $categoryKind,
            'rankings' => $rankings,
            'charts' => $charts,
            'activity' => $this->talentActivity($talentEvent),
            'updated_at' => now()->toIso8601String(),
            'generated_at' => now()->format('M d, Y g:i A'),
            'can_export' => $this->scope->canExportPreliminaryResults($admin),
            'party_performance' => [],
            'turnout_sections' => $this->turnoutSectionsForExport($admin, $talentEvent),
            'live_payload' => null,
            'can_view_live_counts' => $canViewVoteCounts,
            'actions' => [
                'pause' => route('admin.live.talent.pause', $talentEvent),
                'resume' => route('admin.live.talent.resume', $talentEvent),
                'close' => route('admin.live.talent.close', $talentEvent),
                'export' => route('admin.live.talent.export', $talentEvent),
                'results' => route('admin.results.talent.show', $talentEvent),
                'participants' => route('admin.talent-participants.index', ['event' => $talentEvent->id]),
                'competition' => route('admin.talent-competition.show', $talentEvent),
            ],
        ];
    }

    /**
     * @return Collection<int, Election>
     */
    protected function visibleElections(User $admin): Collection
    {
        $query = Election::query()
            ->withCount(['votes', 'candidates', 'categories'])
            ->orderByDesc('voting_starts_at');

        if (! $admin->isSuperAdmin()) {
            $assignedId = $this->scope->assignment($admin)?->election_id;

            $query->where(function ($inner) use ($admin, $assignedId) {
                $inner->where('created_by', $admin->id);

                if ($assignedId) {
                    $inner->orWhere('id', $assignedId);
                }
            });
        }

        return $query->get();
    }

    /**
     * @return Collection<int, TalentEvent>
     */
    protected function visibleTalentEvents(User $admin): Collection
    {
        $query = TalentEvent::query()
            ->withCount(['votes', 'entries'])
            ->orderByDesc('event_date');

        if (! $admin->isSuperAdmin()) {
            // Match Live Monitoring: creator OR linked to assigned election
            // (assignment may still point at a soft-deleted election id).
            $assignedId = $this->scope->assignment($admin)?->election_id;

            $query->where(function ($inner) use ($admin, $assignedId) {
                $inner->where('created_by', $admin->id);

                if ($assignedId) {
                    $inner->orWhere('election_id', $assignedId);
                }
            });
        }

        return $query->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeElection(Election $election, User $admin): array
    {
        $stats = $this->electionStats($election, $admin);
        $isLive = $this->isElectionLive($election);

        return [
            'key' => $this->eventKey('election', $election->id),
            'type' => 'election',
            'id' => $election->id,
            'slug' => $election->slug,
            'name' => $election->title,
            'category' => 'Student Election',
            'voting_status' => $this->electionVotingStatus($election),
            'voting_status_tone' => $this->electionStatusTone($election),
            'starts_at' => $election->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $election->voting_ends_at?->format('M d, Y g:i A'),
            'total_votes' => $stats['total_votes'],
            'turnout_percent' => $stats['turnout_percent'],
            'is_live' => $isLive,
            'view_label' => $isLive ? 'View Live Results' : 'View Results',
            'show_url' => route('admin.results.election.show', $election),
            'export_url' => route('admin.results.election.export', ['election' => $election, 'format' => 'csv']),
            'display_date' => $election->voting_ends_at?->format('M d, Y') ?? $election->voting_starts_at?->format('M d, Y'),
            'sort_at' => ($election->voting_starts_at ?? $election->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeTalentEvent(TalentEvent $talentEvent, User $admin): array
    {
        $stats = $this->talentStats($talentEvent, $admin);
        $isLive = $this->isTalentLive($talentEvent);

        return [
            'key' => $this->eventKey('talent', $talentEvent->id),
            'type' => 'talent',
            'id' => $talentEvent->id,
            'slug' => $talentEvent->slug,
            'name' => $talentEvent->title,
            'category' => $this->talentCategoryLabel($talentEvent),
            'voting_status' => $talentEvent->displayStatusLabel(),
            'talent_category' => $talentEvent->talent_category?->label(),
            'voting_method' => $talentEvent->votingMethodLabel(),
            'number_of_winners' => (int) ($talentEvent->number_of_winners ?? 3),
            'contestants' => $stats['participants'],
            'voting_status_tone' => $this->talentStatusTone($talentEvent),
            'starts_at' => $talentEvent->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $talentEvent->voting_ends_at?->format('M d, Y g:i A'),
            'total_votes' => $stats['total_votes'],
            'turnout_percent' => $stats['turnout_percent'],
            'is_live' => $isLive,
            'view_label' => $isLive ? 'View Live Results' : 'View Results',
            'show_url' => route('admin.results.talent.show', $talentEvent),
            'export_url' => route('admin.results.talent.export', ['talentEvent' => $talentEvent, 'format' => 'csv']),
            'display_date' => $talentEvent->event_date?->format('M d, Y') ?? $talentEvent->voting_ends_at?->format('M d, Y'),
            'sort_at' => ($talentEvent->voting_starts_at ?? $talentEvent->event_date ?? $talentEvent->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array{total_votes: int, turnout_percent: float, participants: int}
     */
    protected function electionStats(Election $election, User $admin): array
    {
        $assigned = $this->scope->assignedElection($admin);

        if ($assigned && (int) $assigned->id === (int) $election->id) {
            $stats = $this->scope->statistics($admin);

            return [
                'total_votes' => (int) ($stats['votes_cast'] ?? 0),
                'turnout_percent' => (float) ($stats['turnout_percent'] ?? 0),
                'participants' => (int) ($stats['eligible_voters'] ?? 0),
            ];
        }

        $participants = $election->eligibleVoterCount();
        $uniqueVoters = (int) $election->votes()->distinct('user_id')->count('user_id');

        return [
            'total_votes' => (int) $election->votes()->count(),
            'turnout_percent' => $participants > 0
                ? round(($uniqueVoters / $participants) * 100, 1)
                : 0.0,
            'participants' => $participants,
        ];
    }

    /**
     * @return array{
     *     total_votes: int,
     *     turnout_percent: float,
     *     participants: int,
     *     unique_voters: int,
     *     eligible_voters: int,
     *     approved_entries: int,
     *     pending_entries: int,
     *     rejected_entries: int,
     *     total_entries: int
     * }
     */
    protected function talentStats(TalentEvent $talentEvent, User $admin): array
    {
        // Official competition results must match rankings: count every vote
        // cast for this talent event (do not zero-out via empty admin scopes).
        $totalVotes = (int) TalentEventVote::query()
            ->where('talent_event_id', $talentEvent->id)
            ->count();

        $uniqueVoters = (int) TalentEventVote::query()
            ->where('talent_event_id', $talentEvent->id)
            ->distinct('user_id')
            ->count('user_id');

        $entryCounts = TalentEventEntry::query()
            ->where('talent_event_id', $talentEvent->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $approved = (int) ($entryCounts[TalentEventEntry::STATUS_APPROVED] ?? 0);
        $pending = (int) ($entryCounts[TalentEventEntry::STATUS_PENDING] ?? 0);
        $rejected = (int) ($entryCounts[TalentEventEntry::STATUS_REJECTED] ?? 0);
        $totalEntries = (int) $entryCounts->sum();

        $eligible = $this->talentEligibleVoterCount($admin);

        return [
            'total_votes' => $totalVotes,
            'unique_voters' => $uniqueVoters,
            'eligible_voters' => $eligible,
            'turnout_percent' => $eligible > 0 ? round(($uniqueVoters / $eligible) * 100, 1) : 0.0,
            'participants' => $approved,
            'approved_entries' => $approved,
            'pending_entries' => $pending,
            'rejected_entries' => $rejected,
            'total_entries' => $totalEntries,
        ];
    }

    protected function talentEligibleVoterCount(User $admin): int
    {
        if ($admin->isSuperAdmin()) {
            return (int) User::query()
                ->where('role', UserRole::Student)
                ->where('is_active', true)
                ->count();
        }

        $scoped = (int) $this->scope->scopedStudentsQuery($admin)->count();
        if ($scoped > 0) {
            return $scoped;
        }

        $manageable = (int) $this->scope->manageableStudentsQuery($admin)->count();
        if ($manageable > 0) {
            return $manageable;
        }

        return (int) User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true)
            ->count();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function electionRankings(Election $election): array
    {
        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->with(['category', 'partylist'])
            ->withCount(['votes' => fn ($q) => $q->where('election_id', $election->id)])
            ->get();

        $categoryTotals = $candidates
            ->groupBy('election_category_id')
            ->map(fn (Collection $group) => (int) $group->sum('votes_count'));

        $ranked = $candidates
            ->map(function (Candidate $candidate) use ($categoryTotals) {
                $position = $candidate->category?->name ?? $candidate->position ?? 'Position';
                $categoryVotes = (int) ($categoryTotals[$candidate->election_category_id] ?? 0);
                $votes = (int) $candidate->votes_count;

                return [
                    'id' => $candidate->id,
                    'name' => $candidate->display_name,
                    'position' => $position,
                    'party' => $candidate->partylist?->name ?: ($candidate->party_or_group ?: 'Independent'),
                    'partylist_id' => $candidate->partylist_id,
                    'party_color' => $candidate->partylist?->color,
                    'party_logo_url' => $candidate->partylist && EventImageUrl::hasUploadedImage($candidate->partylist->logo_path)
                        ? EventImageUrl::resolve($candidate->partylist->logo_path)
                        : null,
                    'votes' => $votes,
                    'percent' => $categoryVotes > 0 ? round(($votes / $categoryVotes) * 100, 1) : 0.0,
                    'sort_order' => (int) ($candidate->category?->sort_order ?? 999),
                    'position_rank' => $this->positionRank($position),
                    'status' => 'Active',
                    'photo_url' => EventImageUrl::hasUploadedImage($candidate->photo_path)
                        ? EventImageUrl::resolve($candidate->photo_path)
                        : null,
                ];
            })
            ->sortBy(fn (array $row) => [
                $row['position_rank'],
                $row['sort_order'],
                strtolower($row['position']),
                -$row['votes'],
                strtolower($row['name']),
            ])
            ->values();

        $rankByCategory = [];
        $output = [];

        foreach ($ranked as $row) {
            $categoryKey = $row['position'];
            $rankByCategory[$categoryKey] = ($rankByCategory[$categoryKey] ?? 0) + 1;
            $rank = $rankByCategory[$categoryKey];
            $maxVotes = $ranked->where('position', $categoryKey)->max('votes');
            $isWinner = $rank === 1 && $row['votes'] > 0 && $row['votes'] === $maxVotes;

            $output[] = array_merge($row, [
                'rank' => $rank,
                'status' => $isWinner ? 'Winner' : ($row['votes'] > 0 ? 'Trailing' : 'No votes'),
            ]);
        }

        return $output;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array{label: string, name: string, party: ?string, votes: int, percent: float}>
     */
    protected function electionWinners(array $rankings): array
    {
        $winners = [];

        foreach ($rankings as $row) {
            if (($row['rank'] ?? 0) !== 1 || ($row['votes'] ?? 0) <= 0) {
                continue;
            }

            $winners[] = [
                'label' => $row['position'],
                'name' => $row['name'],
                'party' => $row['party'] ?? null,
                'partylist_id' => $row['partylist_id'] ?? null,
                'votes' => $row['votes'],
                'percent' => $row['percent'],
                'photo_url' => $row['photo_url'] ?? null,
                'candidate_id' => $row['id'] ?? null,
            ];
        }

        return $winners;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function talentRankings(TalentEvent $talentEvent): array
    {
        $entries = $talentEvent->approvedEntries()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('display_name')
            ->get();

        $totalVotes = (int) $entries->sum('votes_count');

        return $entries->values()->map(function (TalentEventEntry $entry, int $index) use ($totalVotes, $talentEvent) {
            $votes = (int) $entry->votes_count;
            $rank = $index + 1;

            return [
                'id' => $entry->id,
                'rank' => $rank,
                'name' => $entry->display_name,
                'position' => $entry->grade_level
                    ? 'Grade '.$entry->grade_level.($entry->section ? ' · '.$entry->section : '')
                    : 'Contestant',
                'category' => $entry->talentCategoryLabel()
                    ?? $talentEvent->talent_category?->label()
                    ?? '—',
                'party' => '—',
                'votes' => $votes,
                'percent' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0.0,
                'status' => $rank === 1 && $votes > 0 ? 'Winner' : ($votes > 0 ? 'Finalist' : 'No votes'),
                'photo' => $entry->photoUrl(),
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array{label: string, name: string, votes: int, percent: float}>
     */
    protected function talentWinners(TalentEvent $talentEvent, array $rankings): array
    {
        $kind = $this->talentCategoryKind($talentEvent);

        if ($kind === 'intramurals') {
            return $this->intramuralWinners($rankings);
        }

        if ($kind === 'talent_competition') {
            return $this->talentPlacementWinners($talentEvent, $rankings);
        }

        return $this->genericPlacementWinners($rankings, max(3, min(5, count($rankings))));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array{label: string, name: string, votes: int, percent: float}>
     */
    protected function talentPlacementWinners(TalentEvent $talentEvent, array $rankings): array
    {
        $winnerCount = max(1, (int) ($talentEvent->number_of_winners ?? 3));
        $labels = ['Champion', '1st Runner-up', '2nd Runner-up', '3rd Runner-up', '4th Runner-up'];
        $winners = [];

        for ($index = 0; $index < $winnerCount; $index++) {
            $row = $rankings[$index] ?? null;
            $winners[] = [
                'label' => $labels[$index] ?? 'Winner '.($index + 1),
                'name' => $row['name'] ?? '—',
                'votes' => (int) ($row['votes'] ?? 0),
                'percent' => (float) ($row['percent'] ?? 0),
            ];
        }

        foreach (array_slice($rankings, 0, 10) as $index => $row) {
            $winners[] = [
                'label' => 'Top '.($index + 1),
                'name' => $row['name'],
                'votes' => $row['votes'],
                'percent' => $row['percent'],
                'group' => 'top_ten',
            ];
        }

        return $winners;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array{label: string, name: string, votes: int, percent: float}>
     */
    protected function intramuralWinners(array $rankings): array
    {
        $winners = $this->genericPlacementWinners($rankings, 3, [
            'Champion',
            '1st Place',
            '2nd Place',
        ]);

        $specialLabels = [
            'best muse' => 'Best Muse',
            'mr.' => 'Mr. Intramurals',
            'mr ' => 'Mr. Intramurals',
            'ms.' => 'Ms. Intramurals',
            'ms ' => 'Ms. Intramurals',
        ];

        foreach ($specialLabels as $needle => $label) {
            $match = collect($rankings)->first(function (array $row) use ($needle) {
                $haystack = strtolower($row['name'].' '.($row['position'] ?? ''));

                return str_contains($haystack, $needle);
            });

            $winners[] = [
                'label' => $label,
                'name' => $match['name'] ?? '—',
                'votes' => (int) ($match['votes'] ?? 0),
                'percent' => (float) ($match['percent'] ?? 0),
                'group' => 'special',
            ];
        }

        return $winners;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @param  array<int, string>|null  $labels
     * @return array<int, array{label: string, name: string, votes: int, percent: float}>
     */
    protected function genericPlacementWinners(array $rankings, int $limit, ?array $labels = null): array
    {
        $labels ??= ['Champion', '1st Place', '2nd Place'];
        $winners = [];

        for ($i = 0; $i < $limit; $i++) {
            $row = $rankings[$i] ?? null;
            $winners[] = [
                'label' => $labels[$i] ?? 'Rank '.($i + 1),
                'name' => $row['name'] ?? '—',
                'votes' => (int) ($row['votes'] ?? 0),
                'percent' => (float) ($row['percent'] ?? 0),
            ];
        }

        return $winners;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<string, mixed>
     */
    protected function electionCharts(array $rankings): array
    {
        $leaders = collect($rankings)
            ->groupBy('position')
            ->map(fn (Collection $group) => $group->sortByDesc('votes')->first())
            ->sortByDesc('votes')
            ->values()
            ->take(8);

        $labels = $leaders->pluck('name')->map(fn ($name) => Str::limit((string) $name, 12))->all();
        $values = $leaders->pluck('votes')->map(fn ($v) => (int) $v)->all();
        $percents = $leaders->pluck('percent')->map(fn ($v) => (float) $v)->all();
        $yMax = max($values ?: [0]);
        $yMax = $yMax > 0 ? (int) ceil($yMax * 1.15) : 10;

        return [
            'bar' => [
                'labels' => $labels,
                'values' => $values,
                'yMax' => $yMax,
                'yTicks' => [0, (int) round($yMax / 2), $yMax],
            ],
            'pie' => [
                'labels' => $labels,
                'values' => $percents,
                'yMax' => 100,
                'yTicks' => [0, 50, 100],
                'valueSuffix' => '%',
            ],
            'doughnut' => [
                'labels' => $labels,
                'values' => $values,
                'yMax' => $yMax,
                'yTicks' => [0, (int) round($yMax / 2), $yMax],
            ],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<string, mixed>
     */
    protected function talentCharts(array $rankings): array
    {
        $top = collect($rankings)->take(8);
        $labels = $top->pluck('name')->map(fn ($name) => Str::limit((string) $name, 12))->all();
        $values = $top->pluck('votes')->map(fn ($v) => (int) $v)->all();
        $percents = $top->pluck('percent')->map(fn ($v) => (float) $v)->all();
        $yMax = max($values ?: [0]);
        $yMax = $yMax > 0 ? (int) ceil($yMax * 1.15) : 10;

        return [
            'bar' => [
                'labels' => $labels,
                'values' => $values,
                'yMax' => $yMax,
                'yTicks' => [0, (int) round($yMax / 2), $yMax],
            ],
            'pie' => [
                'labels' => $labels,
                'values' => $percents,
                'yMax' => 100,
                'yTicks' => [0, 50, 100],
                'valueSuffix' => '%',
            ],
            'doughnut' => [
                'labels' => $labels,
                'values' => $values,
                'yMax' => $yMax,
                'yTicks' => [0, (int) round($yMax / 2), $yMax],
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, icon: string, state: string}>
     */
    protected function electionLifecycleSteps(Election $election): array
    {
        $current = match (true) {
            $election->status === ElectionStatus::Archived => 'archived',
            $election->public_results_published => 'results_published',
            $this->isElectionFinal($election) => 'completed',
            $election->is_paused => 'paused',
            $this->isElectionLive($election) || $election->status === ElectionStatus::Active => 'open',
            default => 'draft',
        };

        $order = ['draft', 'open', 'paused', 'completed', 'results_published', 'archived'];
        $currentIndex = array_search($current, $order, true);

        $steps = [
            ['key' => 'draft', 'label' => 'Draft', 'icon' => '📝'],
            ['key' => 'open', 'label' => 'Open', 'icon' => '🟢'],
            ['key' => 'paused', 'label' => 'Paused', 'icon' => '⏸'],
            ['key' => 'completed', 'label' => 'Completed', 'icon' => '✅'],
            ['key' => 'results_published', 'label' => 'Results Published', 'icon' => '🏆'],
            ['key' => 'archived', 'label' => 'Archived', 'icon' => '📦'],
        ];

        return collect($steps)->map(function (array $step) use ($order, $currentIndex) {
            $index = array_search($step['key'], $order, true);
            $state = $index === $currentIndex
                ? 'current'
                : ($index !== false && $currentIndex !== false && $index < $currentIndex ? 'completed' : 'upcoming');

            return [...$step, 'state' => $state];
        })->all();
    }

    /**
     * @return array<int, array{label: string, at: ?string, tone: string}>
     */
    protected function electionActivity(Election $election): array
    {
        $election->loadMissing('resultsPublisher');
        $events = [];

        if ($election->voting_starts_at) {
            $events[] = [
                'label' => 'Voting Started',
                'at' => $election->voting_starts_at->toIso8601String(),
                'display' => $election->voting_starts_at->format('M d, Y g:i A'),
                'tone' => 'emerald',
            ];
        }

        $this->auditTimelineEvents($election->title, ['pause', 'paused', 'resume', 'resumed'])
            ->each(function (array $item) use (&$events) {
                $events[] = $item;
            });

        if ($election->voting_ends_at) {
            $events[] = [
                'label' => 'Voting Closed',
                'at' => $election->voting_ends_at->toIso8601String(),
                'display' => $election->voting_ends_at->format('M d, Y g:i A'),
                'tone' => 'rose',
            ];
        }

        if ($election->public_results_published && $election->results_published_at) {
            $events[] = [
                'label' => 'Results Published',
                'at' => $election->results_published_at->toIso8601String(),
                'display' => $election->results_published_at->format('M d, Y g:i A')
                    .($election->resultsPublisher ? ' · '.$election->resultsPublisher->name : ''),
                'tone' => 'violet',
            ];
        } elseif ($election->results_locked || $this->isElectionFinal($election)) {
            $reviewAt = $election->voting_ends_at ?? now();
            $events[] = [
                'label' => 'Results Under Review',
                'at' => $reviewAt->toIso8601String(),
                'display' => 'Awaiting administrator publication',
                'tone' => 'amber',
            ];
        }

        return collect($events)
            ->sortBy('at')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{label: string, at: ?string, tone: string}>
     */
    /**
     * Hourly (last 24h) and daily (last 14 days) vote counts for live monitoring.
     *
     * @return array{hourly: array<string, mixed>, daily: array<string, mixed>}
     */
    protected function talentVoteTimeline(TalentEvent $talentEvent): array
    {
        $since = now()->subDays(14)->startOfDay();

        $votes = TalentEventVote::query()
            ->where('talent_event_id', $talentEvent->id)
            ->where('voted_at', '>=', $since)
            ->get(['voted_at']);

        $hourlyLabels = [];
        $hourlyValues = [];

        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i)->startOfHour();
            $end = $hour->copy()->endOfHour();
            $hourlyLabels[] = $hour->format('H:00');
            $hourlyValues[] = $votes->filter(
                fn ($vote) => $vote->voted_at && $vote->voted_at->between($hour, $end)
            )->count();
        }

        $dailyLabels = [];
        $dailyValues = [];

        for ($i = 13; $i >= 0; $i--) {
            $day = now()->subDays($i)->startOfDay();
            $end = $day->copy()->endOfDay();
            $dailyLabels[] = $day->format('M d');
            $dailyValues[] = $votes->filter(
                fn ($vote) => $vote->voted_at && $vote->voted_at->between($day, $end)
            )->count();
        }

        $hourlyMax = max($hourlyValues ?: [0]);
        $hourlyMax = $hourlyMax > 0 ? (int) ceil($hourlyMax * 1.15) : 10;
        $dailyMax = max($dailyValues ?: [0]);
        $dailyMax = $dailyMax > 0 ? (int) ceil($dailyMax * 1.15) : 10;

        return [
            'hourly' => [
                'labels' => $hourlyLabels,
                'values' => $hourlyValues,
                'yMax' => $hourlyMax,
                'yTicks' => [0, (int) round($hourlyMax / 2), $hourlyMax],
            ],
            'daily' => [
                'labels' => $dailyLabels,
                'values' => $dailyValues,
                'yMax' => $dailyMax,
                'yTicks' => [0, (int) round($dailyMax / 2), $dailyMax],
            ],
        ];
    }

    protected function talentActivity(TalentEvent $talentEvent): array
    {
        $events = [];

        if ($talentEvent->voting_starts_at) {
            $events[] = [
                'label' => 'Voting Started',
                'at' => $talentEvent->voting_starts_at->toIso8601String(),
                'display' => $talentEvent->voting_starts_at->format('M d, Y g:i A'),
                'tone' => 'emerald',
            ];
        }

        $this->auditTimelineEvents($talentEvent->title, ['open voting', 'publish', 'results'])
            ->each(function (array $item) use (&$events) {
                $events[] = $item;
            });

        if ($talentEvent->voting_ends_at) {
            $events[] = [
                'label' => 'Voting Closed',
                'at' => $talentEvent->voting_ends_at->toIso8601String(),
                'display' => $talentEvent->voting_ends_at->format('M d, Y g:i A'),
                'tone' => 'rose',
            ];
        }

        if ($talentEvent->results_published_at) {
            $events[] = [
                'label' => 'Results Generated',
                'at' => $talentEvent->results_published_at->toIso8601String(),
                'display' => $talentEvent->results_published_at->format('M d, Y g:i A'),
                'tone' => 'violet',
            ];
        }

        return collect($events)
            ->sortBy('at')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $needles
     * @return Collection<int, array{label: string, at: string, display: string, tone: string}>
     */
    protected function auditTimelineEvents(string $subject, array $needles): Collection
    {
        return AuditLog::query()
            ->where('action', 'like', '%'.$subject.'%')
            ->latest()
            ->limit(20)
            ->get()
            ->filter(function (AuditLog $log) use ($needles) {
                $action = strtolower($log->action ?? '');

                foreach ($needles as $needle) {
                    if (str_contains($action, strtolower($needle))) {
                        return true;
                    }
                }

                return false;
            })
            ->map(function (AuditLog $log) {
                $action = strtolower($log->action ?? '');

                $label = match (true) {
                    str_contains($action, 'pause') && ! str_contains($action, 'resume') => 'Voting Paused',
                    str_contains($action, 'resume') => 'Voting Resumed',
                    str_contains($action, 'publish') => 'Results Generated',
                    str_contains($action, 'open voting') => 'Voting Started',
                    default => Str::title($log->action ?? 'Event update'),
                };

                return [
                    'label' => $label,
                    'at' => $log->created_at?->toIso8601String() ?? now()->toIso8601String(),
                    'display' => $log->created_at?->format('M d, Y g:i A') ?? '—',
                    'tone' => 'amber',
                ];
            })
            ->values();
    }

    protected function eventKey(string $type, int $id): string
    {
        return $type.':'.$id;
    }

    protected function talentCategoryLabel(TalentEvent $event): string
    {
        if ($event->talent_category) {
            return $event->talent_category->label();
        }

        if ($this->talentCategoryKind($event) === 'intramurals') {
            return 'Intramurals';
        }

        return match ($event->type?->value) {
            'talent_competition' => 'Talent Competition',
            default => 'Event Voting',
        };
    }

    /**
     * @param  array{total_votes: int, turnout_percent: float, participants: int}  $stats
     * @return array<string, mixed>
     */
    protected function talentEventSettings(TalentEvent $event, array $stats): array
    {
        $statusCounts = TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $totalParticipants = (int) $statusCounts->sum();

        $mostViewed = TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->orderByDesc('view_count')
            ->first(['display_name', 'performance_title', 'view_count']);

        $categoryStats = TalentEventEntry::query()
            ->where('talent_event_id', $event->id)
            ->whereNotNull('talent_category')
            ->selectRaw('talent_category, COUNT(*) as aggregate')
            ->groupBy('talent_category')
            ->pluck('aggregate', 'talent_category')
            ->map(fn ($count, $category) => [
                'category' => \App\Enums\TalentCategory::tryFrom($category)?->label() ?? $category,
                'count' => (int) $count,
            ])
            ->values()
            ->all();

        return [
            'talent_category' => $event->talent_category?->label(),
            'event_type' => $event->type?->label(),
            'venue' => $event->venue,
            'performance_duration' => $event->performanceDurationLabel(),
            'voting_method' => $event->votingMethodLabel(),
            'max_contestants' => $event->contestantLimitLabel(),
            'contestants' => $stats['participants'],
            'total_participants' => $totalParticipants,
            'approved_participants' => (int) ($statusCounts['approved'] ?? 0),
            'pending_participants' => (int) ($statusCounts['pending'] ?? 0),
            'rejected_participants' => (int) ($statusCounts['rejected'] ?? 0),
            'most_viewed' => $mostViewed ? [
                'name' => $mostViewed->display_name,
                'title' => $mostViewed->performance_title,
                'views' => (int) $mostViewed->view_count,
            ] : null,
            'category_stats' => $categoryStats,
            'number_of_winners' => (int) ($event->number_of_winners ?? 3),
            'display_status' => $event->displayStatusLabel(),
            'total_votes' => $stats['total_votes'],
            'turnout_percent' => $stats['turnout_percent'],
        ];
    }

    protected function talentCategoryKind(TalentEvent $event): string
    {
        $title = strtolower($event->title ?? '');

        if (str_contains($title, 'intramural')) {
            return 'intramurals';
        }

        return match ($event->type?->value) {
            'talent_competition' => 'talent_competition',
            'debate' => 'debate',
            'quiz' => 'quiz',
            default => 'event_voting',
        };
    }

    protected function electionVotingStatus(Election $election): string
    {
        if ($election->public_results_published) {
            return 'Results Published';
        }

        if ($election->status === ElectionStatus::Draft) {
            return 'Draft';
        }

        if ($election->is_paused) {
            return 'Paused';
        }

        if ($this->isElectionFinal($election)) {
            return 'Completed';
        }

        if ($this->isElectionLive($election) || $election->status === ElectionStatus::Active) {
            return 'Open';
        }

        return ucfirst($election->status?->value ?? 'Draft');
    }

    protected function talentVotingStatus(TalentEvent $event): string
    {
        return match ($event->currentStatusKey()) {
            'results_published', 'archived' => 'Completed',
            'voting_paused' => 'Paused',
            'voting_open' => 'Open',
            'voting_closed' => 'Closed',
            default => $event->displayStatusLabel(),
        };
    }

    protected function electionStatusTone(Election $election): string
    {
        return match ($this->electionVotingStatus($election)) {
            'Open' => 'live',
            'Paused' => 'paused',
            'Completed', 'Results Published' => 'closed',
            default => 'idle',
        };
    }

    protected function talentStatusTone(TalentEvent $event): string
    {
        return match ($this->talentVotingStatus($event)) {
            'Open' => 'live',
            'Paused' => 'paused',
            'Completed', 'Closed' => 'closed',
            default => 'idle',
        };
    }

    protected function isElectionLive(Election $election): bool
    {
        return $election->status === ElectionStatus::Active
            && ! $election->is_paused
            && ! $election->annulled_at
            && $this->liveVoting->isVotingWindowOpen($election);
    }

    protected function isElectionFinal(Election $election): bool
    {
        if ($election->status === ElectionStatus::Closed || $election->status === ElectionStatus::Archived) {
            return true;
        }

        return $election->voting_ends_at instanceof Carbon
            && now()->gt($election->voting_ends_at);
    }

    protected function isTalentLive(TalentEvent $event): bool
    {
        if ($event->isArchived()
            || $event->status === TalentEventStatus::ResultsPublished
            || $event->results_published_at !== null) {
            return false;
        }

        // Keep paused competitions on the live monitor so admins can resume.
        if ($event->is_paused) {
            if ($event->status === TalentEventStatus::VotingOpen) {
                return true;
            }

            return ($event->voting_starts_at || $event->voting_ends_at)
                && ! $event->isAfterVotingEnd();
        }

        return $event->isAcceptingVotes();
    }

    protected function isTalentFinal(TalentEvent $event): bool
    {
        return in_array($event->status, [TalentEventStatus::ResultsPublished, TalentEventStatus::Completed], true)
            || $event->votingHasClosed();
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

        if (str_contains($normalized, 'representative')) {
            return 7;
        }

        return 100;
    }

    /**
     * Supplemental presentation data for HTML/PDF export views only.
     *
     * @return array<string, mixed>
     */
    public function buildExportPresentation(array $detail, User $admin, Election|TalentEvent|null $source = null): array
    {
        $summary = $detail['summary'] ?? [];
        $participants = (int) ($summary['participants'] ?? 0);
        $turnoutPercent = (float) ($summary['turnout_percent'] ?? 0);
        $totalVotes = (int) ($summary['total_votes'] ?? 0);
        $studentsVoted = (int) round($participants * ($turnoutPercent / 100));

        if ($source instanceof Election && $this->scope->assignedElection($admin)?->id === $source->id) {
            $breakdown = $this->scope->voterBreakdown($admin);
            $participants = (int) ($breakdown['eligible'] ?? $participants);
            $studentsVoted = (int) ($breakdown['voted'] ?? $studentsVoted);
            $turnoutPercent = $participants > 0
                ? round(($studentsVoted / $participants) * 100, 1)
                : $turnoutPercent;
        }

        $winners = collect($detail['winners'] ?? [])->reject(fn ($w) => ($w['group'] ?? null) === 'top_ten')->values();
        $rankings = $detail['rankings'] ?? [];
        $charts = $detail['charts'] ?? [];

        $isOfficial = ! ($detail['is_live'] ?? false)
            && ($detail['is_published'] ?? false)
            && (
                ($source instanceof Election && $source->public_results_published)
                || ($source instanceof TalentEvent && $source->status === TalentEventStatus::ResultsPublished)
            );

        return [
            'report_id' => 'RPT-'.strtoupper(Str::random(4)).'-'.now()->format('Ymd-His'),
            'generated_by' => $admin->name ?? $admin->account_id ?? 'System Administrator',
            'generated_role' => $admin->staffRole?->name ?? ($admin->isSuperAdmin() ? 'Super Administrator' : 'Operations Admin'),
            'academic_year' => SchoolBranding::academicYear(),
            'semester' => SchoolBranding::semester(),
            'school_name' => SchoolBranding::schoolName(),
            'system_name' => SchoolBranding::systemName(),
            'is_official' => $isOfficial,
            'extended_summary' => [
                'registered_students' => $participants,
                'students_voted' => $studentsVoted,
                'total_votes' => $totalVotes,
                'turnout_percent' => $turnoutPercent,
                'valid_votes' => $totalVotes,
                'invalid_votes' => 0,
                'total_winners' => (int) ($summary['winners_count'] ?? $winners->count()),
            ],
            'party_performance' => $this->partyPerformanceFromRankings($rankings, $winners->all()),
            'turnout_sections' => $this->turnoutSectionsForExport($admin, $source),
            'has_chart_data' => $this->exportHasChartData($charts),
            'winning_candidates' => $winners->map(fn (array $winner) => [
                'name' => $winner['name'] ?? '—',
                'position' => $winner['label'] ?? '—',
                'party' => $winner['party'] ?? 'Independent',
                'votes' => (int) ($winner['votes'] ?? 0),
                'percent' => (float) ($winner['percent'] ?? 0),
            ])->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @param  array<int, array<string, mixed>>  $winners
     * @return array<int, array{party: string, total_votes: int, seats_won: int, percent: float}>
     */
    protected function partyPerformanceFromRankings(array $rankings, array $winners): array
    {
        if ($rankings === []) {
            return [];
        }

        // Group by the campaign FK when present; fall back to the legacy
        // free-text party label for historical rows without a partylist_id.
        $groupKey = static function (array $row): string {
            $partylistId = $row['partylist_id'] ?? null;
            if ($partylistId !== null) {
                return 'pl:'.$partylistId;
            }

            $party = trim((string) ($row['party'] ?? 'Independent'));

            return 'label:'.($party === '' || $party === '—' ? 'Independent' : $party);
        };

        $partyLabel = static function (array $row): string {
            $party = trim((string) ($row['party'] ?? 'Independent'));

            return $party === '' || $party === '—' ? 'Independent' : $party;
        };

        $groups = [];
        foreach ($rankings as $row) {
            $key = $groupKey($row);
            $groups[$key] ??= [
                'party' => $partyLabel($row),
                'partylist_id' => $row['partylist_id'] ?? null,
                'color' => $row['party_color'] ?? null,
                'logo_url' => $row['party_logo_url'] ?? null,
                'total_votes' => 0,
                'seats_won' => 0,
            ];
            $groups[$key]['total_votes'] += (int) ($row['votes'] ?? 0);
        }

        foreach ($winners as $winner) {
            if (($winner['votes'] ?? 0) <= 0) {
                continue;
            }
            $key = $groupKey($winner);
            if (isset($groups[$key])) {
                $groups[$key]['seats_won']++;
            }
        }

        $totalVotes = max(array_sum(array_column($groups, 'total_votes')), 1);

        return collect($groups)
            ->map(fn (array $group) => [
                'party' => $group['party'],
                'partylist_id' => $group['partylist_id'],
                'color' => $group['color'],
                'logo_url' => $group['logo_url'],
                'total_votes' => $group['total_votes'],
                'seats_won' => $group['seats_won'],
                'percent' => round(($group['total_votes'] / $totalVotes) * 100, 1),
            ])
            ->sortByDesc('total_votes')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{grade: string, section: string, registered: int, voted: int, turnout_percent: float}>
     */
    protected function turnoutSectionsForExport(User $admin, Election|TalentEvent|null $source): array
    {
        if (! $source instanceof Election) {
            return [];
        }

        if ($this->scope->assignedElection($admin)?->id !== $source->id && ! $admin->isSuperAdmin()) {
            return [];
        }

        return $this->scope->turnoutBySection($admin)
            ->map(function (array $row) {
                return [
                    'grade' => (string) ($row['grade'] ?? 'All'),
                    'section' => (string) ($row['section'] ?? 'General'),
                    'registered' => (int) ($row['registered'] ?? $row['eligible'] ?? 0),
                    'voted' => (int) ($row['voted'] ?? 0),
                    'turnout_percent' => (float) ($row['turnout_percent'] ?? $row['turnout'] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $charts
     */
    protected function exportHasChartData(array $charts): bool
    {
        foreach (['bar', 'pie', 'doughnut'] as $key) {
            $values = $charts[$key]['values'] ?? [];
            if (collect($values)->sum() > 0) {
                return true;
            }
        }

        return false;
    }
}
