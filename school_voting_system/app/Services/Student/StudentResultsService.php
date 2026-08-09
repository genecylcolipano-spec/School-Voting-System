<?php

namespace App\Services\Student;

use App\Enums\ElectionStatus;
use App\Enums\TalentEventStatus;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Models\User;
use App\Support\EventImageUrl;
use App\Support\WinnerSpotlightBuilder;
use Illuminate\Support\Collection;

class StudentResultsService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function listEvents(): Collection
    {
        $events = collect();

        foreach ($this->visibleElections() as $election) {
            $events->push($this->summarizeElection($election));
        }

        foreach ($this->visibleTalentEvents() as $talentEvent) {
            $events->push($this->summarizeTalentEvent($talentEvent));
        }

        return $events
            ->sortByDesc(fn (array $event) => $event['sort_at'] ?? now()->toDateTimeString())
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardPreview(): array
    {
        $ongoingElection = Election::query()
            ->acceptingVotes()
            ->orderBy('voting_ends_at')
            ->first();

        if ($ongoingElection) {
            return [
                'mode' => 'voting_open',
                'title' => 'Voting is Open',
                'message' => 'The election is currently active.',
                'submessage' => 'Please cast your vote before voting closes.',
                'event_name' => $ongoingElection->title,
                'button_label' => 'Vote Now',
                'button_url' => route('student.voting.show', $ongoingElection),
            ];
        }

        $ongoingTalent = TalentEvent::query()
            ->publishedToStudents()
            ->where('status', TalentEventStatus::VotingOpen)
            ->where(function ($query) {
                $query->whereNull('voting_ends_at')
                    ->orWhere('voting_ends_at', '>=', now());
            })
            ->orderBy('voting_ends_at')
            ->first();

        if ($ongoingTalent) {
            return [
                'mode' => 'voting_open',
                'title' => 'Voting is Open',
                'message' => 'A voting event is currently active.',
                'submessage' => 'Please cast your vote before voting closes.',
                'event_name' => $ongoingTalent->title,
                'button_label' => 'Vote Now',
                'button_url' => route('student.talent-voting.show', $ongoingTalent),
            ];
        }

        $latestPublishedElection = Election::query()
            ->where('public_results_published', true)
            ->orderByDesc('results_published_at')
            ->first();

        if ($latestPublishedElection) {
            return [
                'mode' => 'published',
                'title' => 'Official Results Available',
                'message' => 'The official election results have been published.',
                'submessage' => 'Congratulations to all winners!',
                'event_name' => $latestPublishedElection->title,
                'button_label' => 'View Results',
                'button_url' => route('student.results.election.show', $latestPublishedElection),
            ];
        }

        $latestPublishedTalent = TalentEvent::query()
            ->publishedToStudents()
            ->whereIn('status', [TalentEventStatus::ResultsPublished, TalentEventStatus::Completed])
            ->orderByDesc('results_published_at')
            ->first();

        if ($latestPublishedTalent) {
            return [
                'mode' => 'published',
                'title' => 'Official Results Available',
                'message' => 'The official election results have been published.',
                'submessage' => 'Congratulations to all winners!',
                'event_name' => $latestPublishedTalent->title,
                'button_label' => 'View Results',
                'button_url' => route('student.results.talent.show', $latestPublishedTalent),
            ];
        }

        $reviewElection = Election::query()
            ->where('public_results_published', false)
            ->where(function ($query) {
                $query->whereIn('status', [ElectionStatus::Closed, ElectionStatus::Archived])
                    ->orWhere(function ($inner) {
                        $inner->whereNotNull('voting_ends_at')
                            ->where('voting_ends_at', '<', now());
                    });
            })
            ->orderByDesc('voting_ends_at')
            ->first();

        if ($reviewElection) {
            return [
                'mode' => 'review',
                'title' => 'Results',
                'message' => 'The election has ended.',
                'submessage' => 'Official results are currently under review by the election committee.',
                'button_label' => 'View Results',
                'button_url' => route('student.results.index'),
            ];
        }

        $reviewTalent = TalentEvent::query()
            ->publishedToStudents()
            ->whereNotIn('status', [TalentEventStatus::ResultsPublished, TalentEventStatus::Completed])
            ->whereNotNull('voting_ends_at')
            ->where('voting_ends_at', '<', now())
            ->orderByDesc('voting_ends_at')
            ->first();

        if ($reviewTalent) {
            return [
                'mode' => 'review',
                'title' => 'Results',
                'message' => 'The voting event has ended.',
                'submessage' => 'Official results are currently under review by the election committee.',
                'button_label' => 'View Results',
                'button_url' => route('student.results.index'),
            ];
        }

        return [
            'mode' => 'none',
            'title' => 'Results',
            'message' => 'No official results yet.',
            'submessage' => 'Results will appear after voting events are completed.',
            'button_label' => 'View Results',
            'button_url' => route('student.results.index'),
        ];
    }

    public function assertVisibleTalentEvent(TalentEvent $talentEvent): void
    {
        abort_unless($talentEvent->published_to_students, 404);
    }

    /**
     * @return array<string, mixed>
     */
    public function electionDetail(Election $election): array
    {
        $election->loadMissing('resultsPublisher');
        $official = $this->isElectionOfficial($election);
        $studentStatus = $this->electionStudentStatus($election);
        $rawRankings = $official ? $this->electionRankings($election) : [];
        $winnerSpotlight = $official ? WinnerSpotlightBuilder::fromRankings($rawRankings) : [];
        $winners = $official ? $this->electionWinners($rawRankings) : [];
        $rankings = $official ? $rawRankings : $this->sanitizeRankingsForStudent($rawRankings);

        return [
            'type' => 'election',
            'slug' => $election->slug,
            'name' => $election->title,
            'category' => 'Student Election',
            'category_kind' => 'election',
            'icon' => '🗳',
            'description' => $election->description,
            'student_status' => $studentStatus['label'],
            'student_status_tone' => $studentStatus['tone'],
            'is_official' => $official,
            'is_open' => ! $official && $studentStatus['label'] !== 'Upcoming',
            'starts_at' => $election->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $election->voting_ends_at?->format('M d, Y g:i A'),
            'event_date' => $election->voting_ends_at?->format('M d, Y') ?? $election->voting_starts_at?->format('M d, Y'),
            'results_published_at' => $official ? $election->results_published_at?->format('M d, Y') : null,
            'results_published_time' => $official ? $election->results_published_at?->format('g:i A') : null,
            'results_published_by' => $official ? $election->resultsPublisher?->name : null,
            'winners' => $winners,
            'winner_spotlight' => $winnerSpotlight,
            'primary_winner' => WinnerSpotlightBuilder::primaryWinner($winnerSpotlight),
            'winners_layout' => 'election',
            'rankings' => $rankings,
            'statistics' => $official ? $this->electionStatistics($election) : null,
            'top_finalists' => [],
            'special_awards' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function talentDetail(TalentEvent $talentEvent): array
    {
        $this->assertVisibleTalentEvent($talentEvent);

        $official = $this->isTalentOfficial($talentEvent);
        $studentStatus = $this->talentStudentStatus($talentEvent);
        $categoryKind = $this->talentCategoryKind($talentEvent);
        $rawRankings = $official ? $this->talentRankings($talentEvent) : [];
        $rankings = $this->sanitizeRankingsForStudent($rawRankings);
        $winners = $official ? $this->sanitizeWinnersForStudent($this->talentWinners($talentEvent, $rawRankings)) : [];

        $topFinalists = [];
        $specialAwards = [];

        if ($official && $categoryKind === 'talent_competition') {
            $topFinalists = collect($rankings)->take(10)->values()->all();
            $winners = collect($winners)->reject(fn ($w) => ($w['group'] ?? '') === 'top_ten')->values()->all();
        }

        if ($official && $categoryKind === 'intramurals') {
            $specialAwards = collect($winners)->where('group', 'special')->values()->all();
            $winners = collect($winners)->reject(fn ($w) => ($w['group'] ?? '') === 'special')->values()->all();
        }

        return [
            'type' => 'talent',
            'slug' => $talentEvent->slug,
            'name' => $talentEvent->title,
            'category' => $this->talentCategoryLabel($talentEvent),
            'category_kind' => $categoryKind,
            'icon' => $this->talentIcon($talentEvent, $categoryKind),
            'description' => $talentEvent->description,
            'student_status' => $studentStatus['label'],
            'student_status_tone' => $studentStatus['tone'],
            'is_official' => $official,
            'is_open' => ! $official && $studentStatus['label'] !== 'Upcoming',
            'starts_at' => $talentEvent->voting_starts_at?->format('M d, Y g:i A'),
            'ends_at' => $talentEvent->voting_ends_at?->format('M d, Y g:i A'),
            'event_date' => $talentEvent->event_date?->format('M d, Y'),
            'winners' => $winners,
            'winners_layout' => $categoryKind,
            'rankings' => $rankings,
            'statistics' => $official ? $this->talentStatistics($talentEvent) : null,
            'top_finalists' => $topFinalists,
            'special_awards' => $specialAwards,
        ];
    }

    public function hasAnyEvents(): bool
    {
        return $this->visibleElections()->isNotEmpty()
            || $this->visibleTalentEvents()->isNotEmpty();
    }

    public function hasCompletedEvents(): bool
    {
        return $this->listEvents()->contains(fn (array $event) => ($event['is_official'] ?? false) === true);
    }

    public function isElectionOfficial(Election $election): bool
    {
        return $election->shouldShowOfficialResultsToStudents();
    }

    public function isTalentOfficial(TalentEvent $talentEvent): bool
    {
        return in_array($talentEvent->status, [TalentEventStatus::ResultsPublished, TalentEventStatus::Completed], true);
    }

    /**
     * @return Collection<int, Election>
     */
    protected function visibleElections(): Collection
    {
        return Election::query()
            ->where(function ($query) {
                $query->whereNot('status', ElectionStatus::Draft)
                    ->orWhere('public_results_published', true);
            })
            ->orderByDesc('voting_starts_at')
            ->get();
    }

    /**
     * @return Collection<int, TalentEvent>
     */
    protected function visibleTalentEvents(): Collection
    {
        return TalentEvent::query()
            ->publishedToStudents()
            ->orderByDesc('event_date')
            ->get();
    }

    /**
     * @return array{label: string, tone: string}
     */
    protected function electionStudentStatus(Election $election): array
    {
        if ($this->isElectionOfficial($election)) {
            return ['label' => 'Results Published', 'tone' => 'closed'];
        }

        if ($this->isElectionEnded($election)) {
            return ['label' => 'Under Review', 'tone' => 'review'];
        }

        if ($election->isBeforeVotingStart()) {
            return ['label' => 'Upcoming', 'tone' => 'idle'];
        }

        if ($election->isAcceptingVotes()) {
            return ['label' => 'Open', 'tone' => 'live'];
        }

        return ['label' => 'Open', 'tone' => 'live'];
    }

    protected function isElectionEnded(Election $election): bool
    {
        if (in_array($election->status, [ElectionStatus::Closed, ElectionStatus::Archived], true)) {
            return true;
        }

        return $election->voting_ends_at !== null && now()->gt($election->voting_ends_at);
    }

    /**
     * @return array{label: string, tone: string}
     */
    protected function talentStudentStatus(TalentEvent $talentEvent): array
    {
        if ($this->isTalentOfficial($talentEvent)) {
            return ['label' => 'Results Published', 'tone' => 'closed'];
        }

        if ($talentEvent->votingHasClosed()) {
            return ['label' => 'Under Review', 'tone' => 'review'];
        }

        if (in_array($talentEvent->status, [TalentEventStatus::Scheduled, TalentEventStatus::EntriesOpen], true)
            || ($talentEvent->voting_starts_at && now()->lt($talentEvent->voting_starts_at))) {
            return ['label' => 'Upcoming', 'tone' => 'idle'];
        }

        return ['label' => 'Open', 'tone' => 'live'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeElection(Election $election): array
    {
        $status = $this->electionStudentStatus($election);
        $official = $this->isElectionOfficial($election);

        return [
            'type' => 'election',
            'key' => 'election:'.$election->id,
            'id' => $election->id,
            'slug' => $election->slug,
            'name' => $election->title,
            'category' => 'Student Election',
            'category_kind' => 'election',
            'icon' => '🗳',
            'student_status' => $status['label'],
            'student_status_tone' => $status['tone'],
            'is_official' => $official,
            'can_view_results' => $election->shouldShowOfficialResultsToStudents(),
            'can_vote' => $election->isAcceptingVotes(),
            'vote_url' => route('student.voting.show', $election),
            'date' => $election->voting_ends_at?->format('M d, Y') ?? $election->voting_starts_at?->format('M d, Y'),
            'show_url' => route('student.results.election.show', $election),
            'sort_at' => ($official && $election->results_published_at)
                ? $election->results_published_at->toDateTimeString()
                : ($election->voting_ends_at ?? $election->voting_starts_at ?? $election->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function summarizeTalentEvent(TalentEvent $talentEvent): array
    {
        $status = $this->talentStudentStatus($talentEvent);
        $official = $this->isTalentOfficial($talentEvent);
        $categoryKind = $this->talentCategoryKind($talentEvent);

        return [
            'type' => 'talent',
            'key' => 'talent:'.$talentEvent->id,
            'id' => $talentEvent->id,
            'slug' => $talentEvent->slug,
            'name' => $talentEvent->title,
            'category' => $this->talentCategoryLabel($talentEvent),
            'category_kind' => $categoryKind,
            'icon' => $this->talentIcon($talentEvent, $categoryKind),
            'student_status' => $status['label'],
            'student_status_tone' => $status['tone'],
            'is_official' => $official,
            'can_view_results' => $official,
            'date' => $talentEvent->event_date?->format('M d, Y'),
            'show_url' => route('student.results.talent.show', $talentEvent),
            'sort_at' => ($official && $talentEvent->results_published_at)
                ? $talentEvent->results_published_at->toDateTimeString()
                : ($talentEvent->event_date ?? $talentEvent->created_at)?->toDateTimeString(),
        ];
    }

    /**
     * @return array{turnout_percent: float, total_votes: int, participants: int}
     */
    protected function electionStatistics(Election $election): array
    {
        return [
            'turnout_percent' => $election->turnoutPercent(),
            'total_votes' => (int) $election->votes()->count(),
            'participants' => $election->eligibleVoterCount(),
        ];
    }

    /**
     * @return array{turnout_percent: float, total_votes: int, participants: int}
     */
    protected function talentStatistics(TalentEvent $talentEvent): array
    {
        $totalVotes = (int) $talentEvent->votes()->count();
        $uniqueVoters = (int) $talentEvent->votes()->distinct('user_id')->count('user_id');
        $eligible = (int) User::query()
            ->where('role', \App\Enums\UserRole::Student)
            ->where('is_active', true)
            ->count();

        return [
            'turnout_percent' => $eligible > 0 ? round(($uniqueVoters / $eligible) * 100, 1) : 0.0,
            'total_votes' => $totalVotes,
            'participants' => (int) $talentEvent->approvedEntries()->count(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array<string, mixed>>
     */
    protected function sanitizeRankingsForStudent(array $rankings): array
    {
        return collect($rankings)->map(function (array $row) {
            unset($row['votes'], $row['percent']);

            return $row;
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $winners
     * @return array<int, array<string, mixed>>
     */
    protected function sanitizeWinnersForStudent(array $winners): array
    {
        return collect($winners)->map(function (array $winner) {
            unset($winner['votes'], $winner['percent']);

            return $winner;
        })->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function electionRankings(Election $election): array
    {
        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->with('category')
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
                    'party' => $candidate->party_or_group ?: 'Independent',
                    'votes' => $votes,
                    'percent' => $categoryVotes > 0 ? round(($votes / $categoryVotes) * 100, 1) : 0.0,
                    'sort_order' => (int) ($candidate->category?->sort_order ?? 999),
                    'position_rank' => $this->positionRank($position),
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
                'status' => $isWinner ? 'Winner' : ($row['votes'] > 0 ? 'Finalist' : '—'),
            ]);
        }

        return $output;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array<string, mixed>>
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

        return $entries->values()->map(function (TalentEventEntry $entry, int $index) use ($totalVotes) {
            $votes = (int) $entry->votes_count;
            $rank = $index + 1;

            return [
                'id' => $entry->id,
                'rank' => $rank,
                'name' => $entry->display_name,
                'position' => $entry->grade_level
                    ? 'Grade '.$entry->grade_level.($entry->section ? ' · '.$entry->section : '')
                    : 'Contestant',
                'votes' => $votes,
                'percent' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0.0,
                'status' => $rank === 1 && $votes > 0 ? 'Winner' : ($votes > 0 ? 'Finalist' : '—'),
            ];
        })->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array<string, mixed>>
     */
    protected function talentWinners(TalentEvent $talentEvent, array $rankings): array
    {
        $kind = $this->talentCategoryKind($talentEvent);

        if ($kind === 'intramurals') {
            return $this->intramuralWinners($rankings);
        }

        if ($kind === 'talent_competition') {
            return $this->talentPlacementWinners($rankings);
        }

        return $this->genericPlacementWinners($rankings, max(3, min(5, count($rankings))));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rankings
     * @return array<int, array<string, mixed>>
     */
    protected function talentPlacementWinners(array $rankings): array
    {
        $labels = ['Champion', '1st Runner-up', '2nd Runner-up'];
        $winners = [];

        foreach ($labels as $index => $label) {
            $row = $rankings[$index] ?? null;
            $winners[] = [
                'label' => $label,
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
     * @return array<int, array<string, mixed>>
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
     * @return array<int, array<string, mixed>>
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

    protected function talentCategoryLabel(TalentEvent $event): string
    {
        if ($this->talentCategoryKind($event) === 'intramurals') {
            return 'Intramurals';
        }

        return match ($event->type?->value) {
            'talent_competition' => 'Talent Competition',
            default => 'Event Voting',
        };
    }

    protected function talentCategoryKind(TalentEvent $event): string
    {
        $title = strtolower($event->title ?? '');

        if (str_contains($title, 'intramural')) {
            return 'intramurals';
        }

        return match ($event->type?->value) {
            'talent_competition' => 'talent_competition',
            default => 'event_voting',
        };
    }

    protected function talentIcon(TalentEvent $event, string $categoryKind): string
    {
        if ($categoryKind === 'intramurals') {
            return '🏀';
        }

        if ($categoryKind === 'talent_competition') {
            return str_contains(strtolower($event->title ?? ''), 'idol') ? '🎤' : '🎭';
        }

        return '🎪';
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

        return 99;
    }
}
