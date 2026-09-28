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
use App\Support\SchoolBranding;
use App\Support\WinnerSpotlightBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $showBreakdown = $request->boolean('breakdown');

        $report = null;
        $exportUrls = null;
        $exportLockedReason = null;
        $winningParty = null;
        $turnoutSections = collect();
        $statistics = [
            'eligible_voters' => 0,
            'voted_students' => 0,
            'votes_cast' => 0,
            'turnout_percent' => 0.0,
        ];

        if ($election instanceof Election) {
            $overview = $this->results->electionReportOverview($election, $user);
            $spotlight = WinnerSpotlightBuilder::fromRankings($overview['rankings']);
            $winningParty = collect($overview['party_performance'])
                ->sortByDesc(fn (array $party) => [
                    (int) ($party['seats_won'] ?? 0),
                    (int) ($party['total_votes'] ?? 0),
                ])
                ->first();

            $statistics = [
                'eligible_voters' => $overview['summary']['participants'],
                'voted_students' => 0,
                'votes_cast' => $overview['summary']['total_votes'],
                'turnout_percent' => $overview['summary']['turnout_percent'],
            ];

            if ($showBreakdown) {
                $turnoutSections = $this->scope->turnoutBySection($user, $election);
            }

            $report = [
                'election_name' => $overview['name'],
                'total_votes' => $overview['summary']['total_votes'],
                'turnout_percent' => $overview['summary']['turnout_percent'],
                'participants' => $overview['summary']['participants'],
                'winners' => $spotlight,
                'party_performance' => $overview['party_performance'],
                'turnout_sections' => $showBreakdown ? $turnoutSections : [],
            ];

            if ($this->scope->canDownloadElectionReportFile($user, $election)) {
                $exportUrls = [
                    'pdf' => route('admin.results.election.export', [$election, 'format' => 'pdf'] + ($showBreakdown ? ['breakdown' => 1] : [])),
                    'excel' => route('admin.results.election.export', [$election, 'format' => 'excel'] + ($showBreakdown ? ['breakdown' => 1] : [])),
                    'print' => route('admin.results.election.export', [$election, 'format' => 'print'] + ($showBreakdown ? ['breakdown' => 1] : [])),
                ];
            } elseif ($this->scope->canExportElectionResults($user, $election)) {
                $exportLockedReason = 'PDF, Excel, and printable reports are available after voting has ended.';
            }
        }

        return view('admin.reports.index', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'election' => $election,
            'elections' => $elections,
            'statistics' => $statistics,
            'turnoutSections' => $turnoutSections,
            'showBreakdown' => $showBreakdown,
            'report' => $report,
            'exportUrls' => $exportUrls,
            'exportLockedReason' => $exportLockedReason,
            'winningParty' => $winningParty,
        ]);
    }

    public function talent(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $events = $this->scope->talentEvents($user);

        $rows = $events->map(function (TalentEvent $event) use ($user) {
            $entries = $event->entries;
            $approved = $entries->where('status', TalentEventEntry::STATUS_APPROVED)->count();
            $rejected = $entries->where('status', TalentEventEntry::STATUS_REJECTED)->count();
            $pending = $entries->where('status', TalentEventEntry::STATUS_PENDING)->count();
            $participants = $event->entries_count ?? $entries->count();
            $votes = $event->votes_count ?? 0;
            $winners = $this->talentRanking->winners($event);
            $canExportFiles = $this->scope->canDownloadTalentReportFile($user, $event);

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
                'can_export_files' => $canExportFiles,
                'export_pdf' => $canExportFiles ? route('admin.results.talent.export', [$event, 'format' => 'pdf']) : null,
                'export_excel' => $canExportFiles ? route('admin.results.talent.export', [$event, 'format' => 'excel']) : null,
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
        $format = $request->string('format')->toString() ?: 'pdf';
        $payload = $this->fundraisingReportPayload($user);
        $filenameBase = 'fundraising-report-'.now()->format('Y-m-d');
        $viewData = $this->fundraisingExportViewData($payload, $user);

        return match ($format) {
            'excel' => $this->fundraisingExcel($payload, $filenameBase),
            'print' => response(view('admin.reports.fundraising-export', array_merge($viewData, [
                'forPrint' => true,
            ]))->render(), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Disposition' => 'inline',
            ]),
            'pdf' => $this->downloadFundraisingPdf(array_merge($viewData, [
                'forPdf' => true,
            ]), $filenameBase.'.pdf'),
            default => abort(404),
        };
    }

    /**
     * @return array{fundraisers: Collection<int, array<string, mixed>>, summary: array<string, int|float>}
     */
    protected function fundraisingReportPayload($user): array
    {
        $campaigns = $this->scope->fundraisersForReports($user)
            ->with([
                'paidDonations' => fn ($query) => $query
                    ->with('donor')
                    ->orderByDesc('paid_at')
                    ->orderByDesc('id'),
            ])
            ->withCount(['donations as paid_donations_count' => fn ($query) => $query->paid()])
            ->orderByDesc('created_at')
            ->get();

        $rows = $campaigns->map(function (Fundraiser $fundraiser) {
            $donors = $fundraiser->paidDonations
                ->map(function (Donation $donation) {
                    return [
                        'name' => $donation->is_anonymous
                            ? 'Anonymous'
                            : ($donation->donor?->name ?? '—'),
                        'is_anonymous' => (bool) $donation->is_anonymous,
                        'role' => $donation->is_anonymous ? '—' : ($donation->donor?->roleLabel() ?? '—'),
                        'amount' => (float) $donation->amount,
                        'method' => $donation->payment_method?->label() ?? '—',
                        'donated_at' => ($donation->paid_at ?? $donation->donated_at)?->format('M d, Y g:i A') ?? '—',
                    ];
                })
                ->values()
                ->all();

            return [
                'title' => $fundraiser->title,
                'status' => $fundraiser->displayStatusLabel(),
                'category' => $fundraiser->category?->label() ?? '—',
                'period' => trim(
                    ($fundraiser->starts_on?->format('M d, Y') ?? '—')
                    .' — '
                    .($fundraiser->ends_on?->format('M d, Y') ?? '—')
                ),
                'donations' => (int) ($fundraiser->paid_donations_count ?? count($donors)),
                'goal' => (float) $fundraiser->goal_amount,
                'raised' => (float) $fundraiser->amount_raised,
                'progress' => $fundraiser->progressPercent(),
                'donors' => $donors,
            ];
        });

        return [
            'fundraisers' => $rows,
            'summary' => [
                'campaigns' => $rows->count(),
                'total_goal' => (float) $rows->sum('goal'),
                'total_raised' => (float) $rows->sum('raised'),
                'total_donations' => (int) $rows->sum('donations'),
            ],
        ];
    }

    /**
     * @param  array{fundraisers: Collection<int, array<string, mixed>>, summary: array<string, int|float>}  $payload
     * @return array<string, mixed>
     */
    protected function fundraisingExportViewData(array $payload, $user): array
    {
        return [
            'fundraisers' => $payload['fundraisers'],
            'summary' => $payload['summary'],
            'generatedAt' => now()->toDayDateTimeString(),
            'signatory' => $user->name,
            'signatoryRole' => $user->roleLabel(),
            'reportId' => 'RPT-FR-'.now()->format('YmdHis'),
            'forPdf' => false,
            'forPrint' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    protected function downloadFundraisingPdf(array $viewData, string $filename): Response
    {
        $fontDir = storage_path('fonts');
        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0755, true);
        }

        $cachedFont = $fontDir.DIRECTORY_SEPARATOR.'MonotypeCorsiva.ttf';
        $sourceFont = public_path('fonts/MonotypeCorsiva.ttf');
        if (! is_file($cachedFont) && is_file($sourceFont)) {
            copy($sourceFont, $cachedFont);
        }

        return Pdf::loadView('admin.reports.fundraising-export', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'chroot' => base_path(),
            ])
            ->download($filename);
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
                ['School', SchoolBranding::schoolName()],
                ['System', SchoolBranding::systemName()],
                ['Semester', SchoolBranding::semester()],
                ['Academic year', SchoolBranding::academicYear()],
                ['Report', 'Official Fundraising Report'],
                ['Generated', now()->toDayDateTimeString()],
                [],
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

            $rows[] = [];
            $rows[] = ['Successful donors (paid only; pending and cancelled are excluded)'];
            $rows[] = ['Campaign', 'Donor', 'Role', 'Amount', 'Method', 'Date'];

            foreach ($payload['fundraisers'] as $row) {
                if (($row['donors'] ?? []) === []) {
                    $rows[] = [$row['title'], 'No successful donations', '', '', '', ''];

                    continue;
                }

                foreach ($row['donors'] as $donor) {
                    $rows[] = [
                        $row['title'],
                        $donor['name'],
                        $donor['role'],
                        $donor['amount'],
                        $donor['method'],
                        $donor['donated_at'],
                    ];
                }
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
