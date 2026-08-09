<?php

namespace App\Services\Admin;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Partylist;
use App\Models\User;
use App\Models\Vote;
use App\Support\EventImageUrl;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminLiveVotingService
{
    public function __construct(
        protected AdminScopeService $scope,
    ) {}

    public function progress(User $admin): array
    {
        $election = $this->scope->assignedElection($admin);

        if (! $election instanceof Election) {
            return [
                'is_live' => false,
                'reason' => 'no_election',
                'message' => 'No election is assigned to your admin account.',
            ];
        }

        if ($election->annulled_at) {
            return [
                'is_live' => false,
                'reason' => 'annulled',
                'message' => 'This election has been annulled.',
                'election_title' => $election->title,
                'election_status' => 'Annulled',
            ];
        }

        if (! $election->status?->isOpenForVoting()) {
            return [
                'is_live' => false,
                'reason' => 'not_active',
                'message' => 'Set the election status to Active to monitor voting here.',
                'election_title' => $election->title,
                'election_status' => ucfirst($election->status?->value ?? 'inactive'),
            ];
        }

        $windowOpen = $this->isVotingWindowOpen($election);
        $stats = $this->scope->statistics($admin);
        $breakdown = $this->scope->voterBreakdown($admin);

        return [
            'is_live' => true,
            'window_open' => $windowOpen,
            'is_paused' => (bool) $election->is_paused,
            'reason' => $windowOpen ? 'live' : $this->windowClosedReason($election),
            'message' => $windowOpen
                ? 'Live vote counts are updating every few seconds.'
                : $this->windowClosedMessage($election),
            'election_title' => $election->title,
            'total_votes' => $stats['votes_cast'],
            'unique_voters' => $breakdown['voted'],
            'eligible_voters' => $breakdown['eligible'],
            'registered_students' => $breakdown['eligible'],
            'turnout_percent' => $stats['turnout_percent'],
            'election_status' => $this->liveStatusLabel($election, $windowOpen),
            'voting_ends_at' => $election->voting_ends_at?->toIso8601String(),
            'voting_starts_at' => $election->voting_starts_at?->toIso8601String(),
            'countdown' => $election->countdownSnapshot(),
            'leading_candidates' => $this->electionLiveMonitoring($election),
            'partylist_comparison' => $this->partylistLiveComparison($election),
            'recent_activity' => $this->recentVotingActivity($election),
            'updated_at' => now()->toIso8601String(),
        ];
    }

    public function isVotingWindowOpen(Election $election, ?Carbon $at = null): bool
    {
        $at ??= now();

        if (! $election->status?->isOpenForVoting() || $election->annulled_at) {
            return false;
        }

        if ($election->voting_starts_at && $at->lt($election->voting_starts_at)) {
            return false;
        }

        if ($election->voting_ends_at && $at->gt($election->voting_ends_at)) {
            return false;
        }

        return true;
    }

    /** @deprecated Use isVotingWindowOpen() */
    public function isVotingPeriodOpen(Election $election, ?Carbon $at = null): bool
    {
        return $this->isVotingWindowOpen($election, $at);
    }

    protected function liveStatusLabel(Election $election, bool $windowOpen): string
    {
        if ($election->is_paused) {
            return 'Paused';
        }

        if (! $windowOpen) {
            return $this->windowClosedStatus($election);
        }

        if ($election->isAcceptingVotes()) {
            return 'Voting Open';
        }

        return ucfirst($election->status?->value ?? 'active');
    }

    protected function windowClosedStatus(Election $election): string
    {
        $now = now();

        if ($election->voting_ends_at && $now->gt($election->voting_ends_at)) {
            return 'Window Ended';
        }

        if ($election->voting_starts_at && $now->lt($election->voting_starts_at)) {
            return 'Not Started';
        }

        return 'Outside Window';
    }

    protected function windowClosedReason(Election $election): string
    {
        $now = now();

        if ($election->voting_ends_at && $now->gt($election->voting_ends_at)) {
            return 'window_ended';
        }

        if ($election->voting_starts_at && $now->lt($election->voting_starts_at)) {
            return 'not_started';
        }

        return 'outside_window';
    }

    protected function windowClosedMessage(Election $election): string
    {
        return match ($this->windowClosedReason($election)) {
            'window_ended' => 'The voting window has ended. Counts below reflect ballots cast so far.',
            'not_started' => 'Voting has not started yet. Counts will update once the window opens.',
            default => 'Outside the configured voting window. Counts below are shown for monitoring.',
        };
    }

    /**
     * @return array<int, array{position: string, candidate: string, party: ?string, votes: int, percent: float, photo_url: ?string, is_leader: bool}>
     */
    protected function electionLiveMonitoring(Election $election): array
    {
        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->with('category')
            ->withCount(['votes' => fn ($query) => $query->where('election_id', $election->id)])
            ->get();

        if ($candidates->isEmpty()) {
            return [];
        }

        $categoryVoteTotals = $candidates
            ->groupBy('election_category_id')
            ->map(fn (Collection $group) => (int) $group->sum('votes_count'));

        $maxVotesByCategory = $candidates
            ->groupBy('election_category_id')
            ->map(fn (Collection $group) => (int) $group->max('votes_count'));

        return $candidates
            ->map(function (Candidate $candidate) use ($categoryVoteTotals, $maxVotesByCategory) {
                $categoryId = $candidate->election_category_id;
                $position = $candidate->category?->name ?? $candidate->position ?? 'Position';
                $categoryVotes = (int) ($categoryVoteTotals[$categoryId] ?? 0);
                $votes = (int) $candidate->votes_count;
                $maxVotes = (int) ($maxVotesByCategory[$categoryId] ?? 0);

                return [
                    'position' => $position,
                    'candidate' => $candidate->display_name,
                    'party' => $candidate->party_or_group,
                    'votes' => $votes,
                    'percent' => $categoryVotes > 0
                        ? round(($votes / $categoryVotes) * 100, 1)
                        : 0.0,
                    'photo_url' => $this->candidatePhotoUrl($candidate),
                    'sort_order' => (int) ($candidate->category?->sort_order ?? 999),
                    'is_leader' => $maxVotes > 0 && $votes === $maxVotes,
                ];
            })
            ->sortBy(fn (array $row) => [
                $this->positionRank($row['position']),
                $row['sort_order'],
                strtolower($row['position']),
                -$row['votes'],
                strtolower($row['candidate']),
            ])
            ->values()
            ->map(fn (array $row) => collect($row)->except('sort_order')->all())
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, acronym: ?string, total_votes: int, percent: float, seats_won: int}>
     */
    protected function partylistLiveComparison(Election $election): array
    {
        $partylists = $election->partylists()
            ->orderBy('name')
            ->get();

        if ($partylists->isEmpty()) {
            return [];
        }

        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->withCount(['votes' => fn ($query) => $query->where('election_id', $election->id)])
            ->get();

        $totalVotes = (int) Vote::query()->where('election_id', $election->id)->count();
        $seatsByParty = $this->seatsWonByParty($candidates);

        return $partylists
            ->map(function (Partylist $partylist) use ($candidates, $totalVotes, $seatsByParty) {
                $matchKeys = $this->partyMatchKeys($partylist);
                $partyVotes = (int) $candidates
                    ->filter(fn (Candidate $candidate) => $this->candidateMatchesParty($candidate, $partylist, $matchKeys))
                    ->sum('votes_count');
                $seatsWon = (int) ($seatsByParty['pl:'.$partylist->id] ?? 0)
                    + collect($matchKeys)->sum(fn (string $key) => $seatsByParty['label:'.$key] ?? 0);

                return [
                    'id' => $partylist->id,
                    'name' => $partylist->name,
                    'acronym' => $partylist->acronym,
                    'logo_url' => $this->partylistLogoUrl($partylist),
                    'total_votes' => $partyVotes,
                    'percent' => $totalVotes > 0 ? round(($partyVotes / $totalVotes) * 100, 1) : 0.0,
                    'seats_won' => (int) $seatsWon,
                ];
            })
            ->sortByDesc('total_votes')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Candidate>  $candidates
     * @return array<string, int>
     */
    protected function seatsWonByParty(Collection $candidates): array
    {
        $seats = [];

        foreach ($candidates->groupBy('election_category_id') as $group) {
            $winner = $group->sortByDesc('votes_count')->first();

            if (! $winner || (int) $winner->votes_count <= 0) {
                continue;
            }

            if ($winner->partylist_id !== null) {
                $key = 'pl:'.$winner->partylist_id;
            } else {
                $partyKey = $this->normalizePartyKey($winner->party_or_group);
                if ($partyKey === '') {
                    continue;
                }
                $key = 'label:'.$partyKey;
            }

            $seats[$key] = ($seats[$key] ?? 0) + 1;
        }

        return $seats;
    }

    protected function candidateMatchesParty(Candidate $candidate, Partylist $partylist, array $matchKeys): bool
    {
        if ($candidate->partylist_id !== null) {
            return (int) $candidate->partylist_id === (int) $partylist->id;
        }

        return in_array($this->normalizePartyKey($candidate->party_or_group), $matchKeys, true);
    }

    /**
     * @return array<int, string>
     */
    protected function partyMatchKeys(Partylist $partylist): array
    {
        return array_values(array_unique(array_filter([
            $this->normalizePartyKey($partylist->acronym),
            $this->normalizePartyKey($partylist->name),
        ])));
    }

    protected function normalizePartyKey(?string $value): string
    {
        return strtolower(trim((string) $value));
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

        return 100;
    }

    /**
     * @return array<int, array{time: ?string, time_display: string, student: string, label: string}>
     */
    protected function recentVotingActivity(Election $election, int $limit = 12): array
    {
        return Vote::query()
            ->where('election_id', $election->id)
            ->with(['voter:id,account_id,name'])
            ->latest('voted_at')
            ->limit($limit)
            ->get()
            ->map(function (Vote $vote) {
                $student = $vote->voter?->account_id
                    ?? $vote->voter?->name
                    ?? 'Student';

                return [
                    'time' => $vote->voted_at?->toIso8601String(),
                    'time_display' => $vote->voted_at?->format('g:i A') ?? '—',
                    'student' => $student,
                    'label' => 'voted',
                ];
            })
            ->all();
    }

    protected function countdownPayload(Election $election): ?array
    {
        return $election->countdownSnapshot();
    }

    protected function candidatePhotoUrl(Candidate $candidate): ?string
    {
        if (! EventImageUrl::hasUploadedImage($candidate->photo_path)) {
            return null;
        }

        return EventImageUrl::resolve($candidate->photo_path);
    }

    protected function partylistLogoUrl(Partylist $partylist): ?string
    {
        if (! EventImageUrl::hasUploadedImage($partylist->logo_path)) {
            return null;
        }

        return EventImageUrl::resolve($partylist->logo_path);
    }

    /** @deprecated Use electionLiveMonitoring() */
    protected function leadingCandidates(Election $election): array
    {
        return $this->electionLiveMonitoring($election);
    }
}

