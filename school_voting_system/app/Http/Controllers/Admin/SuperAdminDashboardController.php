<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\User;
use App\Services\Auth\PasskeyRecoveryQueueService;
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
        protected PasskeyRecoveryQueueService $recoveryQueue,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['staffRole.permissions'])->loadCount('passkeys');
        $recoveryRequests = $this->recoveryQueue->pendingQueue($user);
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
                        ->orWhere('email', 'like', $term);
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
            'recoveryRequests' => $recoveryRequests,
            'statistics' => $this->dashboard->statistics(),
            'systemHealth' => $this->dashboard->systemHealth(),
            'activitySnapshot' => $this->dashboard->activitySnapshot(),
            'staffRoles' => $matrix['roles'],
            'permissions' => $matrix['permissions'],
            'auditLogs' => AuditLog::query()->latest()->limit(25)->get(),
            'passkeys' => Passkey::query()->with('user')->latest()->limit(25)->get(),
            'elections' => $elections,
            'portalUsers' => $portalQuery->paginate(25)->withQueryString(),
            'voterEligibility' => [
                'enrolled' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Enrolled)->count(),
                'probation' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Probation)->count(),
                'withdrawn' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Withdrawn)->count(),
            ],
        ]);
    }
}
