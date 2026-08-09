<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Event;
use App\Models\Candidate;
use App\Models\Fundraiser;
use App\Models\PasskeyRecoveryRequest;
use App\Services\Admin\AdminActivityTimelineService;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminDashboardLiveService;
use App\Services\Admin\AdminLiveVotingService;
use App\Services\Admin\AdminScopeService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminScopeService $scope,
        protected AdminLiveVotingService $liveVoting,
        protected AdminAnalyticsService $analytics,
        protected AdminDashboardLiveService $dashboardLive,
        protected AdminActivityTimelineService $activityTimeline,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['staffRole', 'passkeys']);

        $election = $this->scope->assignedElection($user);
        $statistics = $this->scope->statistics($user);
        $from = $request->date('from')?->toDateString();
        $to = $request->date('to')?->toDateString();
        $actionType = $request->string('action_type')->toString() ?: null;

        $pendingVerifications = $this->scope->pendingVerificationRequests($user);
        $openComplaints = $this->scope->openComplaints($user);
        $pendingTasksCount = $pendingVerifications->count() + $openComplaints->count();

        return view('admin.dashboard', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'assignedRole' => $user->staffRole?->name ?? 'Operations Admin',
            'election' => $election,
            'statistics' => $statistics,
            'voterBreakdown' => $this->scope->voterBreakdown($user),
            'statSparklines' => $this->scope->statCardSparklines($user),
            'partylists' => $this->scope->partylists($user),
            'candidates' => $this->scope->candidates($user)->take(10),
            'talentEvents' => $this->scope->talentEvents($user),
            'schoolEvents' => $this->scope->schoolEvents($user),
            'fundraisers' => $this->scope->fundraisers($user),
            'activityLogs' => $this->scope->myActivityLogsQuery($user, $from, $to, $actionType)->paginate(15)->withQueryString(),
            'actionTypes' => $this->scope->activityLogActionTypes($user),
            'auditorChecks' => $this->scope->auditorChecks($user),
            'countdown' => $this->scope->countdown($election),
            'pendingVerifications' => $pendingVerifications,
            'openComplaints' => $openComplaints,
            'pendingTasksCount' => $pendingTasksCount,
            'roleGuide' => $this->scope->roleGuide($user),
            'activityFilter' => [
                'from' => $request->string('from')->toString(),
                'to' => $request->string('to')->toString(),
                'action_type' => $actionType ?? '',
            ],
            'canPauseElection' => $this->scope->canPauseElection($user),
            'canApprovePosters' => $this->scope->canApprovePosters($user),
            'canVerifyCandidates' => $this->scope->canVerifyCandidates($user),
            'canSendReminders' => $this->scope->canSendReminders($user),
            'canApproveTalentEntries' => $this->scope->canApproveTalentEntries($user),
            'canManageTalentVoting' => $this->scope->canManageTalentVoting($user),
            'canPublishTalentResults' => $this->scope->canPublishTalentResults($user),
            'canCreateTalentEvents' => $this->scope->canCreateTalentEvents($user),
            'canCreateEvents' => $user->can('create', Event::class),
            'analyticsWidgets' => $this->analytics->dashboardWidgets($user),
            'canViewRealtimeTalentCounts' => $this->scope->canViewRealtimeTalentCounts($user),
            'canExportPreliminary' => $this->scope->canExportPreliminaryResults($user),
            'canResolveComplaints' => ! $this->scope->isReadOnly($user) && ! $this->scope->isAuditor($user),
            'canEditElection' => $election && $user->can('update', $election),
            'canCreateCandidate' => $user->can('create', Candidate::class),
            'canCreateElection' => $user->can('create', Election::class),
            'canCreateFundraiser' => $user->can('create', Fundraiser::class),
            'canManageFundraisers' => $user->can('viewAny', Fundraiser::class),
            'isAuditor' => $this->scope->isAuditor($user),
            'isReadOnly' => $this->scope->isReadOnly($user),
            'recentActivityTimeline' => $this->activityTimeline->recentForDashboard($user),
        ]);
    }

    public function liveVoting(Request $request): JsonResponse
    {
        return response()->json($this->liveVoting->progress($request->user()));
    }

    public function live(Request $request): JsonResponse
    {
        return response()->json($this->dashboardLive->snapshot($request->user()));
    }

    public function recovery(Request $request): View
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $recoveryRequests = PasskeyRecoveryRequest::query()
            ->with('user')
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.recovery.index', [
            'user' => $request->user()->loadCount('passkeys'),
            'notificationsCount' => $recoveryRequests->count(),
            'recoveryRequests' => $recoveryRequests,
        ]);
    }
}
