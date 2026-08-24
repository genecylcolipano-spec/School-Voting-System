<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\TalentEvent;
use App\Models\TalentEventEntry;
use App\Services\Admin\AdminResultsService;
use App\Services\Admin\AdminScopeService;
use App\Services\Talent\TalentResultsRankingService;
use App\Support\AdminPortal;
use App\Support\WinnerSpotlightBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AdminResultsService $results,
        protected TalentResultsRankingService $talentRanking,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $elections = $this->scope->reportableElections($user);
        $election = $this->scope->resolveReportElection($user, $this->requestedElectionId($request));
        $statistics = $election
            ? $this->electionStatistics($election)
            : $this->scope->statistics($user);
        $turnoutSections = $this->scope->turnoutBySection($user, $election);

        $report = null;
        $exportUrls = null;
        $winningParty = null;

        if ($election instanceof Election) {
            $detail = $this->results->electionDetail($election, $user);
            $spotlight = WinnerSpotlightBuilder::fromRankings($detail['rankings'] ?? []);
            $winningParty = collect($detail['party_performance'] ?? [])
                ->sortByDesc(fn (array $party) => [
                    (int) ($party['seats_won'] ?? 0),
                    (int) ($party['total_votes'] ?? 0),
                ])
                ->first();

            $report = [
                'election_name' => $detail['name'],
                'total_votes' => $detail['summary']['total_votes'] ?? 0,
                'turnout_percent' => $detail['summary']['turnout_percent'] ?? 0,
                'participants' => $detail['summary']['participants'] ?? 0,
                'winners' => $spotlight,
                'party_performance' => $detail['party_performance'] ?? [],
                'turnout_sections' => $detail['turnout_sections'] ?? [],
            ];

            $exportUrls = [
                'pdf' => route('admin.results.election.export', [$election, 'format' => 'pdf']),
                'excel' => route('admin.results.election.export', [$election, 'format' => 'excel']),
                'print' => route('admin.results.election.export', [$election, 'format' => 'print']),
            ];
        }

        return view('admin.reports.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'election' => $election,
            'elections' => $elections,
            'statistics' => $statistics,
            'turnoutSections' => $turnoutSections,
            'report' => $report,
            'exportUrls' => $exportUrls,
            'winningParty' => $winningParty,
        ]);
    }

    public function talent(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $events = $this->scope->talentEvents($user);

        $rows = $events->map(function (TalentEvent $event) {
            $entries = $event->entries;
            $approved = $entries->where('status', TalentEventEntry::STATUS_APPROVED)->count();
            $rejected = $entries->where('status', TalentEventEntry::STATUS_REJECTED)->count();
            $pending = $entries->where('status', TalentEventEntry::STATUS_PENDING)->count();
            $participants = $event->entries_count ?? $entries->count();
            $votes = $event->votes_count ?? 0;
            $winners = $this->talentRanking->winners($event);

            return [
                'name' => $event->title,
                'slug' => $event->slug,
                'category' => $event->talent_category?->label() ?? '—',
                'status' => $event->displayStatusLabel(),
                'participants' => $participants,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'votes' => $votes,
                'voting_method' => $event->votingMethodLabel(),
                'metric_label' => $this->talentRanking->metricLabel($event),
                'winners' => array_map(fn (array $row) => $row['name'], $winners),
                'winner_count' => count($winners),
                'planned_winners' => (int) ($event->number_of_winners ?? 3),
                'participation' => $participants > 0 ? (int) round(($approved / max($participants, 1)) * 100) : 0,
                'export_pdf' => route('admin.results.talent.export', [$event, 'format' => 'pdf']),
                'export_excel' => route('admin.results.talent.export', [$event, 'format' => 'excel']),
                'show_url' => route('admin.results.talent.show', $event),
            ];
        });

        return view('admin.reports.talent', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'rows' => $rows,
            'totals' => [
                'events' => $rows->count(),
                'participants' => $rows->sum('participants'),
                'approved' => $rows->sum('approved'),
                'votes' => $rows->sum('votes'),
            ],
        ]);
    }

    public function fundraising(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $payload = $this->fundraisingReportPayload($user);

        return view('admin.reports.fundraising', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'fundraisers' => $payload['fundraisers'],
            'summary' => $payload['summary'],
        ]);
    }

    public function exportFundraising(Request $request): Response|StreamedResponse
    {
        $user = $request->user();
        $format = $request->string('format')->toString() ?: 'csv';
        $payload = $this->fundraisingReportPayload($user);
        $filenameBase = 'fundraising-report-'.now()->format('Y-m-d');

        return match ($format) {
            'excel' => $this->fundraisingExcel($payload, $filenameBase),
            'print' => response()->view('admin.reports.fundraising-export', [
                'fundraisers' => $payload['fundraisers'],
                'summary' => $payload['summary'],
                'generatedAt' => now()->toDayDateTimeString(),
                'signatory' => $user->name,
            ]),
            default => $this->fundraisingCsv($payload, $filenameBase),
        };
    }

    /**
     * @return array{fundraisers: Collection<int, array<string, mixed>>, summary: array<string, int|float>}
     */
    protected function fundraisingReportPayload($user): array
    {
        $campaigns = $this->scope->fundraisersForReports($user)
            ->withCount(['donations as paid_donations_count' => fn ($query) => $query->paid()])
            ->orderByDesc('created_at')
            ->get();

        $ids = $campaigns->modelKeys();
        $paidQuery = Donation::query()->paid()->whereIn('fundraiser_id', $ids ?: [0]);

        $rows = $campaigns->map(function (Fundraiser $fundraiser) {
            $paidCount = (int) ($fundraiser->paid_donations_count ?? 0);
            $raised = (float) $fundraiser->amount_raised;

            return [
                'title' => $fundraiser->title,
                'status' => $fundraiser->displayStatusLabel(),
                'donations' => $paidCount,
                'goal' => (float) $fundraiser->goal_amount,
                'raised' => $raised,
                'progress' => $fundraiser->progressPercent(),
            ];
        });

        return [
            'fundraisers' => $rows,
            'summary' => [
                'campaigns' => $rows->count(),
                'total_goal' => (float) $rows->sum('goal'),
                'total_raised' => (float) $rows->sum('raised'),
                'total_donations' => (int) $paidQuery->count(),
            ],
        ];
    }

    /**
     * @param  array{fundraisers: Collection<int, array<string, mixed>>, summary: array<string, int|float>}  $payload
     */
    protected function fundraisingCsv(array $payload, string $filenameBase): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.csv"',
        ];

        return response()->stream(function () use ($payload) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Campaigns', $payload['summary']['campaigns']]);
            fputcsv($handle, ['Total goal', $payload['summary']['total_goal']]);
            fputcsv($handle, ['Total raised', $payload['summary']['total_raised']]);
            fputcsv($handle, ['Paid donations', $payload['summary']['total_donations']]);
            fputcsv($handle, []);
            fputcsv($handle, ['Campaign', 'Status', 'Paid donations', 'Goal', 'Raised', 'Progress %']);

            foreach ($payload['fundraisers'] as $row) {
                fputcsv($handle, [
                    $row['title'],
                    $row['status'],
                    $row['donations'],
                    $row['goal'],
                    $row['raised'],
                    round((float) $row['progress'], 1),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * @param  array{fundraisers: Collection<int, array<string, mixed>>, summary: array<string, int|float>}  $payload
     */
    protected function fundraisingExcel(array $payload, string $filenameBase): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.xls"',
        ];

        return response()->stream(function () use ($payload) {
            $escape = static function (mixed $value): string {
                return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            };

            echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            echo '<?mso-application progid="Excel.Sheet"?>'."\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
            echo '<Worksheet ss:Name="Fundraising"><Table>';

            $rows = [
                ['Campaigns', $payload['summary']['campaigns']],
                ['Total goal', $payload['summary']['total_goal']],
                ['Total raised', $payload['summary']['total_raised']],
                ['Paid donations', $payload['summary']['total_donations']],
                [],
                ['Campaign', 'Status', 'Paid donations', 'Goal', 'Raised', 'Progress %'],
            ];

            foreach ($payload['fundraisers'] as $row) {
                $rows[] = [
                    $row['title'],
                    $row['status'],
                    $row['donations'],
                    $row['goal'],
                    $row['raised'],
                    round((float) $row['progress'], 1),
                ];
            }

            foreach ($rows as $cells) {
                echo '<Row>';
                foreach ($cells as $cell) {
                    $numeric = is_numeric($cell) && ! str_ends_with((string) $cell, '%');
                    $type = $numeric ? 'Number' : 'String';
                    echo '<Cell><Data ss:Type="'.$type.'">'.$escape($cell).'</Data></Cell>';
                }
                echo '</Row>';
            }

            echo '</Table></Worksheet></Workbook>';
        }, 200, $headers);
    }

    /**
     * @return array{eligible_voters: int, voted_students: int, votes_cast: int, turnout_percent: float}
     */
    protected function electionStatistics(Election $election): array
    {
        $eligible = $election->eligibleVoterCount();
        $voted = (int) $election->votes()->distinct('user_id')->count('user_id');
        $votesCast = (int) $election->votes()->count();

        return [
            'eligible_voters' => $eligible,
            'voted_students' => $voted,
            'votes_cast' => $votesCast,
            'turnout_percent' => $eligible > 0 ? round(($voted / $eligible) * 100, 1) : 0.0,
        ];
    }

    protected function requestedElectionId(Request $request): ?int
    {
        $value = $request->query('election');

        return filled($value) ? (int) $value : null;
    }
}
