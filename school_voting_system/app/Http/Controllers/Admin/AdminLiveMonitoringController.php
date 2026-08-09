<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActionType;
use App\Enums\TalentEventStatus;
use App\Http\Controllers\Controller;
use App\Models\TalentEvent;
use App\Services\Admin\AdminLiveMonitoringService;
use App\Services\Admin\AdminResultsService;
use App\Services\Admin\AdminScopeService;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\AuditLogService;
use App\Support\AdminPortal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminLiveMonitoringController extends Controller
{
    public function __construct(
        protected AdminLiveMonitoringService $monitoring,
        protected AdminResultsService $results,
        protected AdminScopeService $scope,
        protected AuditLogService $audit,
        protected PortalNotificationService $notifications,
    ) {}

    public function election(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        abort_unless($user->isSuperAdmin() || $user->isAdmin(), 403);

        $filters = $this->filtersFromRequest($request);
        $cards = $this->monitoring->electionCards($user, $filters);
        $summary = $this->monitoring->summarize($cards, 'election');
        $isSuper = $user->isSuperAdmin();

        return view('admin.live-monitoring.index', [
            'user' => $user,
            'notificationsCount' => $isSuper ? AdminPortal::recoveryCount() : 0,
            'mode' => 'election',
            'title' => $isSuper ? 'Institution Election Monitoring' : 'My Election Monitoring',
            'description' => $isSuper
                ? 'Institution-wide live monitoring of every election across all administrators.'
                : 'Live monitoring of elections you created or manage.',
            'cards' => $cards,
            'urgentCards' => $cards->where('is_urgent', true)->values(),
            'otherCards' => $cards->where('is_urgent', false)->values(),
            'summary' => $summary,
            'filters' => $filters,
            'hasFilters' => $this->hasActiveFilters($filters),
            'administrators' => $this->monitoring->administratorOptions($user),
            'schoolYears' => $this->monitoring->schoolYearOptions($user),
            'statusOptions' => $this->electionStatusOptions(),
            'pollUrl' => route('admin.live.election.poll'),
            'isSuperAdmin' => $isSuper,
        ]);
    }

    public function talent(Request $request): View
    {
        $user = $request->user()->loadCount('passkeys');
        abort_unless($this->scope->canViewRealtimeTalentCounts($user), 403);

        $filters = $this->filtersFromRequest($request);
        $cards = $this->monitoring->talentCards($user, $filters);
        $summary = $this->monitoring->summarize($cards, 'talent');
        $isSuper = $user->isSuperAdmin();

        return view('admin.live-monitoring.talent', [
            'user' => $user,
            'notificationsCount' => $isSuper ? AdminPortal::recoveryCount() : 0,
            'mode' => 'talent',
            'title' => $isSuper ? 'Institution Talent Monitoring' : 'My Talent Monitoring',
            'description' => $isSuper
                ? 'Institution-wide live monitoring of every talent competition across all administrators.'
                : 'Live monitoring of talent competitions you created or manage.',
            'cards' => $cards,
            'urgentCards' => $cards->where('is_urgent', true)->values(),
            'otherCards' => $cards->where('is_urgent', false)->values(),
            'summary' => $summary,
            'filters' => $filters,
            'hasFilters' => $this->hasActiveFilters($filters),
            'administrators' => $this->monitoring->administratorOptions($user),
            'schoolYears' => $this->monitoring->schoolYearOptions($user),
            'statusOptions' => $this->talentStatusOptions(),
            'pollUrl' => route('admin.live.talent.poll'),
            'canManage' => $this->scope->canCreateTalentEvents($user),
            'canExport' => $this->scope->canExportPreliminaryResults($user),
            'isSuperAdmin' => $isSuper,
        ]);
    }

    public function electionPoll(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->isSuperAdmin() || $user->isAdmin(), 403);

        return response()->json($this->monitoring->electionPoll($user, $this->filtersFromRequest($request)));
    }

    public function talentPoll(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($this->scope->canViewRealtimeTalentCounts($user), 403);

        return response()->json($this->monitoring->talentPoll($user, $this->filtersFromRequest($request)));
    }

    public function pause(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'is_paused' => true,
            'status' => TalentEventStatus::VotingOpen,
        ])->save();

        $actor = $request->user();
        $this->audit->record(
            $actor,
            "Paused talent voting: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        $this->notifications->talentVotingPaused($talentEvent->fresh(), $actor);

        return back()->with('success', 'Voting paused. Students cannot cast votes until resumed.');
    }

    public function resume(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'is_paused' => false,
            'status' => TalentEventStatus::VotingOpen,
        ])->save();

        $actor = $request->user();
        $this->audit->record(
            $actor,
            "Resumed talent voting: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        $this->notifications->talentVotingResumed($talentEvent->fresh(), $actor);

        return back()->with('success', 'Voting resumed.');
    }

    public function close(Request $request, TalentEvent $talentEvent): RedirectResponse
    {
        abort_unless($this->scope->canCreateTalentEvents($request->user()), 403);
        $this->scope->assertTalentEventInScope($request->user(), $talentEvent);

        $talentEvent->forceFill([
            'voting_ends_at' => now(),
            'is_paused' => false,
        ])->save();

        $actor = $request->user();
        $this->audit->record(
            $actor,
            "Closed talent voting: {$talentEvent->title}",
            AuditActionType::Election,
            targetType: 'talent_event',
            targetId: $talentEvent->id,
        );

        $this->notifications->talentVotingClosed($talentEvent->fresh(), $actor);

        return back()->with('success', 'Voting closed.');
    }

    public function export(Request $request, TalentEvent $talentEvent): StreamedResponse
    {
        $user = $request->user();
        abort_unless($this->scope->canExportPreliminaryResults($user), 403);
        $this->scope->assertTalentEventInScope($user, $talentEvent);

        $detail = $this->results->talentDetail($talentEvent, $user);
        $filename = 'live-stats-'.Str::slug($talentEvent->slug).'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($detail) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Competition', $detail['name'] ?? '']);
            fputcsv($handle, ['Status', $detail['voting_status'] ?? '']);
            fputcsv($handle, ['Generated', now()->toDayDateTimeString()]);
            fputcsv($handle, []);
            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Participants', $detail['summary']['total_entries'] ?? 0]);
            fputcsv($handle, ['Approved Participants', $detail['summary']['approved_entries'] ?? 0]);
            fputcsv($handle, ['Pending Participants', $detail['summary']['pending_entries'] ?? 0]);
            fputcsv($handle, ['Rejected Participants', $detail['summary']['rejected_entries'] ?? 0]);
            fputcsv($handle, ['Votes Cast', $detail['summary']['total_votes'] ?? 0]);
            fputcsv($handle, ['Unique Voters', $detail['summary']['unique_voters'] ?? 0]);
            fputcsv($handle, ['Participation Rate %', $detail['summary']['turnout_percent'] ?? 0]);
            fputcsv($handle, []);
            fputcsv($handle, ['Rank', 'Contestant', 'Category', 'Votes', 'Percentage']);

            foreach ($detail['rankings'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['rank'] ?? '',
                    $row['name'] ?? '',
                    $row['category'] ?? '',
                    $row['votes'] ?? 0,
                    ($row['percent'] ?? 0).'%',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{
     *     administrator: ?int,
     *     school_year: ?string,
     *     status: ?string,
     *     active_only: bool,
     *     published: bool,
     *     results_pending: bool
     * }
     */
    protected function filtersFromRequest(Request $request): array
    {
        return [
            'administrator' => $request->filled('administrator') ? (int) $request->integer('administrator') : null,
            'school_year' => $request->filled('school_year') ? (string) $request->input('school_year') : null,
            'status' => $request->filled('status') ? (string) $request->input('status') : null,
            'active_only' => $request->boolean('active_only'),
            'published' => $request->boolean('published'),
            'results_pending' => $request->boolean('results_pending'),
        ];
    }

    /**
     * @param  array{
     *     administrator: ?int,
     *     school_year: ?string,
     *     status: ?string,
     *     active_only: bool,
     *     published: bool,
     *     results_pending: bool
     * }  $filters
     */
    protected function hasActiveFilters(array $filters): bool
    {
        return ! empty($filters['administrator'])
            || ! empty($filters['school_year'])
            || ! empty($filters['status'])
            || ! empty($filters['active_only'])
            || ! empty($filters['published'])
            || ! empty($filters['results_pending']);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function electionStatusOptions(): array
    {
        return [
            ['value' => 'scheduled', 'label' => 'Scheduled'],
            ['value' => 'voting_open', 'label' => 'Voting Open'],
            ['value' => 'voting_paused', 'label' => 'Voting Paused'],
            ['value' => 'voting_closed', 'label' => 'Voting Closed'],
            ['value' => 'results_pending', 'label' => 'Results Pending'],
            ['value' => 'published', 'label' => 'Published'],
        ];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    protected function talentStatusOptions(): array
    {
        return [
            ['value' => 'registration_open', 'label' => 'Registration Open'],
            ['value' => 'registration_closed', 'label' => 'Registration Closed'],
            ['value' => 'judging_open', 'label' => 'Judging Open'],
            ['value' => 'voting_open', 'label' => 'Voting Open'],
            ['value' => 'voting_paused', 'label' => 'Voting Paused'],
            ['value' => 'voting_closed', 'label' => 'Voting Closed'],
            ['value' => 'results_pending', 'label' => 'Results Pending'],
            ['value' => 'published', 'label' => 'Published'],
        ];
    }
}
