<?php

namespace App\Services\Admin;

use App\Models\Candidate;
use App\Models\Donation;
use App\Models\Event;
use App\Models\Partylist;
use App\Models\PartylistPoster;
use App\Models\TalentEvent;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Carbon;

class AdminAnalyticsService
{
    public function __construct(protected AdminScopeService $scope) {}

    public function participationGrowth(User $admin): array
    {
        $year = now()->year;
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $eligibleIds = $this->scope->eligibleStudentsQuery()->pluck('id');
        $eligible = max(1, $eligibleIds->count());
        $election = $this->scope->assignedElection($admin);

        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();

        // Distinct (month, voter) pairs pulled in two grouped queries instead of
        // one query per month, then merged in-memory so a student voting in both
        // an election and a talent event is only counted once per month.
        $participantsByMonth = [];

        if ($eligibleIds->isNotEmpty()) {
            $electionRows = Vote::query()
                ->whereIn('user_id', $eligibleIds)
                ->whereBetween('voted_at', [$yearStart, $yearEnd])
                ->when($election, fn ($query) => $query->where('election_id', $election->id))
                ->selectRaw('MONTH(voted_at) as month, user_id')
                ->distinct()
                ->get();

            $talentRows = TalentEventVote::query()
                ->whereIn('user_id', $eligibleIds)
                ->whereBetween('voted_at', [$yearStart, $yearEnd])
                ->selectRaw('MONTH(voted_at) as month, user_id')
                ->distinct()
                ->get();

            foreach ($electionRows->concat($talentRows) as $row) {
                $participantsByMonth[(int) $row->month][$row->user_id] = true;
            }
        }

        $values = [];

        foreach (range(1, 12) as $month) {
            $participants = isset($participantsByMonth[$month]) ? count($participantsByMonth[$month]) : 0;
            $values[] = round(($participants / $eligible) * 100, 1);
        }

        return $this->chartPayload($labels, $values, 100, [0, 25, 50, 75, 100], '%');
    }

    public function fundraisingHistory(User $admin): array
    {
        $year = now()->year;
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();

        $totalsByMonth = Donation::query()
            ->whereBetween('donated_at', [$yearStart, $yearEnd])
            ->selectRaw('MONTH(donated_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $values = [];

        foreach (range(1, 12) as $month) {
            $values[] = round((float) ($totalsByMonth[$month] ?? 0), 2);
        }

        [$yMax, $yTicks] = $this->niceAxis(
            (float) max($values),
            fallbackMax: 800,
            fallbackTicks: [0, 200, 400, 600, 800],
        );

        return $this->chartPayload($labels, $values, $yMax, $yTicks, '', '₱');
    }

    public function votingTurnoutByGradeSection(User $admin): array
    {
        $sections = $this->scope->turnoutBySection($admin);

        if ($sections->isEmpty()) {
            return $this->chartPayload(['No data'], [0], 100, [0, 25, 50, 75, 100], '%');
        }

        return $this->chartPayload(
            $sections->pluck('label')->all(),
            $sections->pluck('turnout')->map(fn ($v) => (float) $v)->all(),
            100,
            [0, 25, 50, 75, 100],
            '%',
        );
    }

    public function campaignEngagement(User $admin): array
    {
        $election = $this->scope->assignedElection($admin);
        $query = Partylist::query()->withCount([
            'posters',
            'posters as approved_posters_count' => fn ($q) => $q->where('status', PartylistPoster::STATUS_APPROVED),
        ]);

        if ($election) {
            $query->whereHas('elections', fn ($q) => $q->whereKey($election->id));
        }

        $partylists = $query->orderByDesc('approved_posters_count')->limit(8)->get();

        if ($partylists->isEmpty()) {
            return $this->chartPayload(['No campaigns'], [0], 100, [0, 25, 50, 75, 100], '%');
        }

        $labels = $partylists->map(fn (Partylist $partylist) => $partylist->acronym ?: $partylist->name)->all();
        $values = $partylists->map(function (Partylist $partylist) {
            $score = ($partylist->approved_posters_count * 25)
                + ($partylist->isPublished() ? 35 : 0)
                + min(40, $partylist->posters_count * 10);

            return (float) min(100, $score);
        })->all();

        return $this->chartPayload($labels, $values, 100, [0, 25, 50, 75, 100], '%');
    }

    /**
     * Vote-based campaign performance for the admin's assigned election, derived
     * from actual candidate votes (not poster activity).
     *
     * @return array<int, array<string, mixed>>
     */
    public function campaignPerformance(User $admin): array
    {
        $election = $this->scope->assignedElection($admin);

        if (! $election) {
            return [];
        }

        $candidates = Candidate::query()
            ->where('election_id', $election->id)
            ->where('is_active', true)
            ->with(['partylist', 'category'])
            ->withCount(['votes' => fn ($q) => $q->where('election_id', $election->id)])
            ->get();

        if ($candidates->isEmpty()) {
            return [];
        }

        // Winner per position (category), keyed by candidate id => position name.
        $winnerPositions = [];
        foreach ($candidates->groupBy('election_category_id') as $group) {
            $winner = $group->sortByDesc('votes_count')->first();
            if ($winner && (int) $winner->votes_count > 0) {
                $winnerPositions[$winner->id] = $winner->category?->name ?? $winner->position ?? 'Position';
            }
        }

        $totalVotes = max(1, (int) $candidates->sum('votes_count'));

        return $candidates
            ->filter(fn (Candidate $candidate) => $candidate->partylist_id !== null)
            ->groupBy('partylist_id')
            ->map(function ($group) use ($winnerPositions, $totalVotes) {
                $first = $group->first();
                $votes = (int) $group->sum('votes_count');
                $winning = $group->filter(fn (Candidate $candidate) => isset($winnerPositions[$candidate->id]));

                return [
                    'partylist_id' => (int) $first->partylist_id,
                    'name' => $first->partylist?->name ?? $first->party_or_group ?? 'Campaign',
                    'acronym' => $first->partylist?->acronym,
                    'color' => $first->partylist?->color,
                    'total_candidates' => $group->count(),
                    'total_votes' => $votes,
                    'winning_candidates' => $winning->count(),
                    'winning_positions' => $winning->map(fn (Candidate $candidate) => $winnerPositions[$candidate->id])->values()->all(),
                    'vote_share' => round(($votes / $totalVotes) * 100, 1),
                ];
            })
            ->sortByDesc('total_votes')
            ->values()
            ->all();
    }

    public function eventAttendanceHistory(User $admin): array
    {
        $year = now()->year;
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $eligibleIds = $this->scope->eligibleStudentsQuery()->pluck('id');
        $values = [];

        foreach (range(1, 12) as $month) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $schoolEvents = Event::query()
                ->whereBetween('event_date', [$start, $end])
                ->count();

            $talentParticipants = $eligibleIds->isEmpty()
                ? 0
                : TalentEventVote::query()
                    ->whereIn('user_id', $eligibleIds)
                    ->whereBetween('voted_at', [$start, $end])
                    ->distinct('user_id')
                    ->count('user_id');

            $values[] = $schoolEvents + $talentParticipants;
        }

        $peak = max(1, (int) max($values));
        $yMax = max(10, (int) (ceil($peak / 5) * 5));
        $step = max(5, (int) round($yMax / 4));
        $yTicks = [0, $step, $step * 2, $step * 3, $yMax];

        return $this->chartPayload($labels, array_map('floatval', $values), $yMax, $yTicks);
    }

    public function dashboardWidgets(User $admin): array
    {
        return [
            'participation' => $this->participationGrowth($admin),
            'fundraising' => $this->fundraisingHistory($admin),
        ];
    }

    public function fullReport(User $admin): array
    {
        return [
            'participation' => $this->participationGrowth($admin),
            'fundraising' => $this->fundraisingHistory($admin),
            'turnout' => $this->votingTurnoutByGradeSection($admin),
            'campaigns' => $this->campaignEngagement($admin),
            'campaignPerformance' => $this->campaignPerformance($admin),
            'events' => $this->eventAttendanceHistory($admin),
            'talentCompetitions' => $this->talentCompetitionSummaries($admin),
            'turnoutSections' => $this->scope->turnoutBySection($admin),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function talentCompetitionSummaries(User $admin): array
    {
        $events = $admin->isSuperAdmin()
            ? TalentEvent::query()->withCount(['entries', 'votes'])->orderByDesc('event_date')->get()
            : $this->scope->talentEvents($admin);

        return $events->map(function (TalentEvent $event) {
            $participants = (int) ($event->entries_count ?? $event->entries()->count());
            $totalVotes = (int) ($event->votes_count ?? $event->votes()->count());

            return [
                'name' => $event->title,
                'talent_category' => $event->talent_category?->label() ?? '—',
                'contestants' => $participants,
                'total_votes' => $totalVotes,
                'voting_method' => $event->votingMethodLabel(),
                'winner_count' => (int) ($event->number_of_winners ?? 3),
                'participation' => $totalVotes > 0 ? $totalVotes : 0,
                'display_status' => $event->displayStatusLabel(),
                'event_date' => $event->event_date?->format('M d, Y'),
            ];
        })->values()->all();
    }

    /**
     * Build a "nice" y-axis (max + evenly spaced ticks) that always sits above
     * the largest value so bars/lines never clip. Falls back to fixed values
     * when there is no data, so an empty chart still renders sane gridlines.
     *
     * @param  array<int, int|float>  $fallbackTicks
     * @return array{0: int|float, 1: array<int, int|float>}
     */
    protected function niceAxis(float $peak, int|float $fallbackMax, array $fallbackTicks): array
    {
        if ($peak <= 0) {
            return [$fallbackMax, $fallbackTicks];
        }

        $magnitude = 10 ** floor(log10($peak));
        $niceMax = (float) (ceil($peak / $magnitude) * $magnitude);
        $step = $niceMax / 4;

        return [
            $niceMax,
            [0, $step, $step * 2, $step * 3, $niceMax],
        ];
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, float|int>  $values
     * @param  array<int, int|float>  $yTicks
     * @return array<string, mixed>
     */
    protected function chartPayload(
        array $labels,
        array $values,
        int|float $yMax,
        array $yTicks,
        string $valueSuffix = '',
        string $valuePrefix = '',
    ): array {
        return [
            'labels' => array_values($labels),
            'values' => array_map('floatval', array_values($values)),
            'yMax' => $yMax,
            'yTicks' => array_values($yTicks),
            'valueSuffix' => $valueSuffix,
            'valuePrefix' => $valuePrefix,
        ];
    }
}
