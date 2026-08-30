<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\User;
use App\Services\Admin\AdminAnalyticsService;
use App\Services\Admin\AdminScopeService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use App\Services\SuperAdmin\SuperAdminDashboardService;
use App\Support\AdminPortal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function __construct(
        protected SuperAdminDashboardService $dashboard,
        protected ElectionLifecycleService $elections,
        protected AdminAnalyticsService $analytics,
        protected AdminScopeService $scope,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['staffRole.permissions'])->loadCount('passkeys');
        $matrix = $this->dashboard->permissionMatrix();
        $portalQuery = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin, UserRole::Student, UserRole::Faculty])
            ->with('staffRole')
            ->withCount('passkeys')
            ->when($request->string('portal_q')->trim()->isNotEmpty(), function ($query) use ($request) {
                $term = '%'.$request->string('portal_q')->trim().'%';
                $query->where(function ($query) use ($term) {
                    $query->where('account_id', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->orderByRaw("CASE role WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 WHEN 'faculty' THEN 2 WHEN 'student' THEN 3 ELSE 4 END")
            ->orderBy('account_id');

        $elections = Election::query()->withCount(['votes', 'candidates'])->latest()->limit(10)->get();
        $elections->each(function (Election $election) {
            $election->setAttribute('dashboard_actions', $this->elections->availableActions($election));
            $election->setAttribute('can_schedule', $this->elections->canSchedule($election));
        });

        return view('admin.super-dashboard', [
            'user' => $user,
            'notificationsCount' => AdminPortal::notificationCount($user),
            'statistics' => $this->dashboard->statistics(),
            'systemHealth' => $this->dashboard->systemHealth(),
            'activitySnapshot' => $this->dashboard->activitySnapshot(),
            'analyticsWidgets' => $this->analytics->dashboardWidgets($user),
            'staffRoles' => $matrix['roles'],
            'permissions' => $matrix['permissions'],
            'auditLogs' => AuditLog::query()->latest()->limit(25)->get(),
            'passkeys' => $this->dashboard->paginatedPasskeys([
                'status' => $request->string('passkey_status')->toString(),
                'q' => $request->string('passkey_q')->toString(),
                'role' => $request->string('passkey_role')->toString(),
            ]),
            'passkeyStatus' => in_array($request->string('passkey_status')->toString(), ['active', 'revoked', 'lost', 'all'], true)
                ? $request->string('passkey_status')->toString()
                : 'active',
            'currentPasskeyId' => (int) $request->session()->get('authenticated_passkey_id', 0),
            'elections' => $elections,
            'reportableElections' => $this->scope->reportableElections($user),
            'complianceElectionId' => $this->scope->resolveReportElection($user, null)?->id,
            'portalUsers' => $portalQuery->paginate(25)->withQueryString(),
            'voterEligibility' => [
                'enrolled' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Enrolled)->count(),
                'probation' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Probation)->count(),
                'withdrawn' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Withdrawn)->count(),
            ],
        ]);
    }
}
