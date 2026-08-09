<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Results\ExportResultsRequest;
use App\Models\Election;
use App\Models\TalentEvent;
use App\Services\Admin\AdminResultsService;
use App\Services\Admin\AdminScopeService;
use App\Services\Election\ElectionIntegrityService;
use App\Support\AdminPortal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminResultsController extends Controller
{
    public function __construct(
        protected AdminResultsService $results,
        protected AdminScopeService $scope,
        protected ElectionIntegrityService $integrity,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        $filter = $request->string('event')->toString() ?: null;
        $events = $this->results->listEvents($user, $filter);

        return view('admin.results.index', [
            'user' => $user,
            'notificationsCount' => $user->isSuperAdmin() ? AdminPortal::recoveryCount() : 0,
            'filterOptions' => $this->results->filterOptions($user),
            'events' => $events,
            'selectedEvent' => $filter,
            'hasEvents' => $this->results->filterOptions($user)->isNotEmpty(),
            'canExport' => $this->scope->canExportPreliminaryResults($user),
        ]);
    }

    public function electionsIndex(Request $request): View
    {
        return $this->scopedIndex($request, 'election', 'Election Results', 'Official results for all student elections in your scope.');
    }

    public function talentIndex(Request $request): View
    {
        return $this->scopedIndex($request, 'talent', 'Talent Competition Results', 'Official results for all talent competitions in your scope.');
    }

    protected function scopedIndex(Request $request, string $type, string $title, string $description): View
    {
        $user = $request->user()->loadCount('passkeys');

        $events = $this->results->listEvents($user)
            ->filter(fn (array $event) => ($event['type'] ?? null) === $type)
            ->values();

        return view('admin.results.scoped', [
            'user' => $user,
            'notificationsCount' => $user->isSuperAdmin() ? AdminPortal::recoveryCount() : 0,
            'mode' => $type,
            'title' => $title,
            'description' => $description,
            'events' => $events,
            'canExport' => $this->scope->canExportPreliminaryResults($user),
        ]);
    }

    public function showElection(Request $request, Election $election): View
    {
        $detail = $this->results->electionDetail($election, $request->user()->loadCount('passkeys'));

        return view('admin.results.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $request->user()->isSuperAdmin() ? AdminPortal::recoveryCount() : 0,
            'detail' => $detail,
            'liveUrl' => route('admin.results.election.live', $election),
            'exportUrls' => $this->exportUrls('election', $election),
            'backUrl' => route('admin.results.index'),
        ]);
    }

    public function showTalent(Request $request, TalentEvent $talentEvent): View
    {
        $detail = $this->results->talentDetail($talentEvent, $request->user()->loadCount('passkeys'));

        return view('admin.results.show', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $request->user()->isSuperAdmin() ? AdminPortal::recoveryCount() : 0,
            'detail' => $detail,
            'liveUrl' => route('admin.results.talent.live', $talentEvent),
            'exportUrls' => $this->exportUrls('talent', $talentEvent),
            'backUrl' => route('admin.results.index'),
        ]);
    }

    public function liveElection(Request $request, Election $election): JsonResponse
    {
        return response()->json($this->results->electionDetail($election, $request->user()));
    }

    public function liveTalent(Request $request, TalentEvent $talentEvent): JsonResponse
    {
        return response()->json($this->results->talentDetail($talentEvent, $request->user()));
    }

    public function exportElection(ExportResultsRequest $request, Election $election, string $format): Response|StreamedResponse
    {
        $user = $request->user();
        $detail = $this->results->electionDetail($election, $user);

        return $this->exportResponse($detail, $format, $election->slug, $user, $election);
    }

    public function exportTalent(ExportResultsRequest $request, TalentEvent $talentEvent, string $format): Response|StreamedResponse
    {
        $user = $request->user();
        $detail = $this->results->talentDetail($talentEvent, $user);

        return $this->exportResponse($detail, $format, $talentEvent->slug, $user, $talentEvent);
    }

    public function verifyElectionIntegrity(Request $request, Election $election): RedirectResponse
    {
        $user = $request->user();
        $this->results->assertCanViewElection($user, $election);

        $result = $this->integrity->verify($election);

        $flashKey = $result['valid'] ? 'success' : ($result['has_hash'] ? 'error' : 'warning');

        return redirect()
            ->route('admin.results.election.show', $election)
            ->with($flashKey, $result['message']);
    }

    public function exportElectionTurnout(ExportResultsRequest $request, Election $election): StreamedResponse
    {
        $user = $request->user();
        $this->results->assertCanViewElection($user, $election);
        $detail = $this->results->electionDetail($election, $user);
        $filenameBase = 'turnout-'.Str::slug($election->slug).'-'.now()->format('Y-m-d');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.'.csv"',
        ];

        return response()->stream(function () use ($detail) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Election', $detail['name'] ?? '']);
            fputcsv($handle, ['Generated', now()->toDayDateTimeString()]);
            fputcsv($handle, []);
            fputcsv($handle, ['Grade', 'Section', 'Registered', 'Voted', 'Turnout %']);

            foreach ($detail['turnout_sections'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['grade'] ?? '',
                    $row['section'] ?? '',
                    $row['registered'] ?? 0,
                    $row['voted'] ?? 0,
                    ($row['turnout_percent'] ?? 0).'%',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * @return array<string, string>
     */
    protected function exportUrls(string $type, Election|TalentEvent $model): array
    {
        $routePrefix = $type === 'election' ? 'admin.results.election.export' : 'admin.results.talent.export';
        $param = $type === 'election' ? 'election' : 'talentEvent';

        return [
            'pdf' => route($routePrefix, [$param => $model, 'format' => 'pdf']),
            'excel' => route($routePrefix, [$param => $model, 'format' => 'excel']),
            'csv' => route($routePrefix, [$param => $model, 'format' => 'csv']),
            'print' => route($routePrefix, [$param => $model, 'format' => 'print']),
        ];
    }

    protected function exportResponse(
        array $detail,
        string $format,
        string $slug,
        \App\Models\User $user,
        Election|TalentEvent|null $source = null,
    ): Response|StreamedResponse {
        $filenameBase = 'results-'.Str::slug($slug).'-'.now()->format('Y-m-d');
        $viewData = [
            'detail' => $detail,
            'presentation' => $this->results->buildExportPresentation($detail, $user, $source),
            'generatedAt' => now()->toDayDateTimeString(),
            'forPrint' => false,
            'forPdf' => false,
        ];

        return match ($format) {
            'csv', 'excel' => $this->csvExport($detail, $filenameBase, $format === 'excel'),
            'pdf' => $this->downloadResultsPdf(
                array_merge($viewData, ['forPdf' => true]),
                $filenameBase.'.pdf',
            ),
            'print' => response(view('admin.results.export', array_merge($viewData, ['forPrint' => true]))->render(), 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'Content-Disposition' => 'inline',
            ]),
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $viewData
     */
    protected function downloadResultsPdf(array $viewData, string $filename): Response
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

        return Pdf::loadView('admin.results.export', $viewData)
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled' => true,
                'fontDir' => $fontDir,
                'fontCache' => $fontDir,
                'chroot' => base_path(),
            ])
            ->download($filename);
    }

    protected function csvExport(array $detail, string $filenameBase, bool $asExcel): StreamedResponse
    {
        $headers = [
            'Content-Type' => $asExcel
                ? 'application/vnd.ms-excel; charset=UTF-8'
                : 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filenameBase.($asExcel ? '.xls' : '.csv').'"',
        ];

        return response()->stream(function () use ($detail) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Event', $detail['name'] ?? '']);
            fputcsv($handle, ['Category', $detail['category'] ?? '']);
            fputcsv($handle, ['Status', $detail['voting_status'] ?? '']);
            fputcsv($handle, []);
            fputcsv($handle, ['Rank', 'Name', 'Position', 'Party', 'Votes', 'Percentage', 'Status']);

            foreach ($detail['rankings'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['rank'] ?? '',
                    $row['name'] ?? '',
                    $row['position'] ?? '',
                    $row['party'] ?? '',
                    $row['votes'] ?? 0,
                    ($row['percent'] ?? 0).'%',
                    $row['status'] ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
