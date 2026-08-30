<?php

namespace App\Services\Admin;

use App\Enums\EventStatus;
use App\Enums\StudentStatus;
use App\Models\Candidate;
use App\Models\Donation;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Models\TalentEventVote;
use App\Models\User;
use App\Models\Vote;
use App\Services\Talent\TalentResultsRankingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    public function __construct(
        protected AdminScopeService $scope,
        protected TalentResultsRankingService $talentRanking,
    ) {}

    public function participationGrowth(User $admin, ?Election $election = null): array
    {
        $year = now()->year;
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $election ??= $this->scope->resolveReportElection($admin, null, preferClosed: false);
        $eligibleIds = $this->eligibleParticipantIds($admin);

        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();
        $monthExpr = $this->monthExpression('voted_at');
        $participantsByMonth = [];

        if ($eligibleIds->isNotEmpty()) {
            $electionRows = Vote::query()
                ->whereIn('user_id', $eligibleIds)
                ->whereBetween('voted_at', [$yearStart, $yearEnd])
                ->when($election, fn ($query) => $query->where('election_id', $election->id))
                ->selectRaw($monthExpr.' as month, user_id')
                ->distinct()
                ->get();

            $talentEventIds = $this->talentEventIdsForAnalytics($admin, $election);
            $talentRows = $talentEventIds->isEmpty()
                ? collect()
                : TalentEventVote::query()
                    ->whereIn('user_id', $eligibleIds)
                    ->whereIn('talent_event_id', $talentEventIds)
                    ->whereBetween('voted_at', [$yearStart, $yearEnd])
                    ->selectRaw($monthExpr.' as month, user_id')
                    ->distinct()
                    ->get();

            foreach ($electionRows->concat($talentRows) as $row) {
                $participantsByMonth[(int) $row->month][$row->user_id] = true;
            }
        }

        $eventCounts = $this->monthlyEventCounts($admin);
        $values = [];

        foreach (range(1, 12) as $month) {
            $voters = isset($participantsByMonth[$month]) ? count($participantsByMonth[$month]) : 0;
            $values[] = round($voters + $eventCounts[$month], 1);
        }

        [$yMax, $yTicks] = $this->niceAxis(
            (float) max($values),
            fallbackMax: 10,
            fallbackTicks: [0, 2.5, 5, 7.5, 10],
        );

        return $this->chartPayload($labels, $values, $yMax, $yTicks);
    }

    public function fundraisingHistory(User $admin): array
    {
        $year = now()->year;
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();
        $fundraiserIds = $this->scope->fundraisersForReports($admin)->pluck('id');
        $monthExpr = $this->monthExpression('donated_at');

        $totalsByMonth = $fundraiserIds->isEmpty()
            ? collect()
            : Donation::query()
                ->paid()
                ->whereIn('fundraiser_id', $fundraiserIds)
                ->whereBetween('donated_at', [$yearStart, $yearEnd])
                ->selectRaw($monthExpr.' as month, SUM(amount) as total')
                ->groupByRaw($monthExpr)
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

    public function votingTurnoutByGradeSection(User $admin, ?Election $election = null): array
    {
        $election ??= $this->scope->resolveReportElection($admin, null, preferClosed: false);
        $sections = $this->scope->turnoutBySection($admin, $election);

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

    public function campaignEngagement(User $admin, ?Election $election = null): array
    {
        $performance = array_slice($this->campaignPerformance($admin, $election), 0, 8);

        if ($performance === []) {
            return $this->chartPayload(['No campaigns'], [0], 100, [0, 25, 50, 75, 100], '%');
        }

        return $this->chartPayload(
            array_map(fn (array $row) => $this->campaignChartLabel($row), $performance),
            array_map(fn (array $row) => (float) $row['vote_share'], $performance),
            100,
            [0, 25, 50, 75, 100],
            '%',
        );
    }

    /**
     * Vote-based campaign performance for the selected report election.
     *
     * @return array<int, array<string, mixed>>
     */
    public function campaignPerformance(User $admin, ?Election $election = null): array
    {
        $election ??= $this->scope->resolveReportElection($admin, null, preferClosed: false);

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

        $winnerPositions = [];
        foreach ($candidates->groupBy('election_category_id') as $group) {
            $maxVotes = (int) $group->max('votes_count');

            if ($maxVotes <= 0) {
                continue;
            }

            foreach ($group->where('votes_count', $maxVotes) as $winner) {
                $winnerPositions[$winner->id] = $winner->category?->name ?? $winner->position ?? 'Position';
            }
        }

        $totalVotes = max(1, (int) $candidates->sum('votes_count'));

        return $candidates
            ->groupBy(fn (Candidate $candidate) => $candidate->partylist_id ?: 0)
            ->map(function ($group) use ($winnerPositions, $totalVotes) {
                $first = $group->first();
                $votes = (int) $group->sum('votes_count');
                $winning = $group->filter(fn (Candidate $candidate) => isset($winnerPositions[$candidate->id]));
                $isIndependent = $first->partylist_id === null;

                return [
                    'partylist_id' => $isIndependent ? null : (int) $first->partylist_id,
                    'name' => $first->partylist?->name ?? 'Independent',
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
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $values = array_values($this->monthlyEventCounts($admin));

        $peak = max(1, (int) max($values));
        $yMax = max(10, (int) (ceil($peak / 5) * 5));
        $step = max(5, (int) round($yMax / 4));
        $yTicks = [0, $step, $step * 2, $step * 3, $yMax];

        return $this->chartPayload($labels, $values, $yMax, $yTicks);
    }

    public function dashboardWidgets(User $admin): array
    {
        $election = $this->scope->resolveReportElection($admin, null, preferClosed: false);

        return [
            'participation' => $this->participationGrowth($admin, $election),
            'fundraising' => $this->fundraisingHistory($admin),
        ];
    }

    public function fullReport(User $admin, ?Election $election = null): array
    {
        $election ??= $this->scope->resolveReportElection($admin, null, preferClosed: false);

        return [
            'election_id' => $election?->id,
            'election_name' => $election?->title,
            'participation' => $this->participationGrowth($admin, $election),
            'fundraising' => $this->fundraisingHistory($admin),
            'turnout' => $this->votingTurnoutByGradeSection($admin, $election),
            'campaigns' => $this->campaignEngagement($admin, $election),
            'campaignPerformance' => $this->campaignPerformance($admin, $election),
            'events' => $this->eventAttendanceHistory($admin),
            'talentCompetitions' => $this->talentCompetitionSummaries($admin),
            'turnoutSections' => $this->scope->turnoutBySection($admin, $election),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function talentCompetitionSummaries(User $admin): array
    {
        $events = $this->scope->talentEvents($admin);

        return $events->map(function (TalentEvent $event) {
            $participants = (int) ($event->entries_count ?? $event->entries()->count());
            $totalVotes = (int) ($event->votes_count ?? $event->votes()->count());
            $winners = $this->talentRanking->winners($event);

            return [
                'name' => $event->title,
                'talent_category' => $event->talent_category?->label() ?? '—',
                'contestants' => $participants,
                'total_votes' => $totalVotes,
                'voting_method' => $event->votingMethodLabel(),
                'metric_label' => $this->talentRanking->metricLabel($event),
                'winner_count' => count($winners),
                'winners' => array_map(fn (array $row) => $row['name'], $winners),
                'display_status' => $event->displayStatusLabel(),
                'event_date' => $event->event_date?->format('M d, Y'),
            ];
        })->values()->all();
    }

    /**
     * School events and talent competitions scheduled in the current year, by month.
     *
     * @return array<int, float>
     */
    protected function monthlyEventCounts(User $admin): array
    {
        $year = now()->year;
        $yearStart = Carbon::create($year, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($year, 12, 31)->endOfYear();
        $monthExpr = $this->monthExpression('event_date');

        $schoolEventsByMonth = $this->scope->schoolEventsQuery($admin)
            ->where('status', '!=', EventStatus::Cancelled)
            ->whereBetween('event_date', [$yearStart, $yearEnd])
            ->selectRaw($monthExpr.' as month, COUNT(*) as total')
            ->groupByRaw($monthExpr)
            ->pluck('total', 'month');

        $talentQuery = TalentEvent::query()->whereBetween('event_date', [$yearStart, $yearEnd]);

        if (! $admin->isSuperAdmin()) {
            $talentIds = $this->scope->talentEvents($admin)->pluck('id');
            $talentQuery->whereIn('id', $talentIds->all() ?: [0]);
        }

        $talentEventsByMonth = $talentQuery
            ->selectRaw($monthExpr.' as month, COUNT(*) as total')
            ->groupByRaw($monthExpr)
            ->pluck('total', 'month');

        $counts = [];

        foreach (range(1, 12) as $month) {
            $counts[$month] = (float) (($schoolEventsByMonth[$month] ?? 0) + ($talentEventsByMonth[$month] ?? 0));
        }

        return $counts;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function eligibleParticipantIds(User $admin)
    {
        $query = $admin->isSuperAdmin()
            ? $this->scope->eligibleStudentsQuery()
            : $this->scope->scopedStudentsQuery($admin)->where('student_status', StudentStatus::Enrolled);

        return $query->pluck('id');
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function talentEventIdsForAnalytics(User $admin, ?Election $election)
    {
        if ($election) {
            return TalentEvent::query()
                ->where('election_id', $election->id)
                ->pluck('id');
        }

        if ($admin->isSuperAdmin()) {
            return TalentEvent::query()->pluck('id');
        }

        return $this->scope->talentEvents($admin)->pluck('id');
    }

    protected function monthExpression(string $column): string
    {
        $wrapped = $this->wrapColumn($column);

        return match (DB::connection()->getDriverName()) {
            'sqlite' => 'CAST(strftime(\'%m\', '.$wrapped.') AS INTEGER)',
            default => 'MONTH('.$wrapped.')',
        };
    }

    protected function wrapColumn(string $column): string
    {
        return DB::getQueryGrammar()->wrap($column);
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
    /**
     * Prefer a short acronym on the chart axis; keep the full name when none exists.
     *
     * @param  array{acronym?: ?string, name?: string}  $row
     */
    protected function campaignChartLabel(array $row): string
    {
        $acronym = trim((string) ($row['acronym'] ?? ''));

        return $acronym !== '' ? $acronym : (string) ($row['name'] ?? 'Campaign');
    }

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
