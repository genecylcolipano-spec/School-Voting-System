<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\SuperAdmin\SuperAdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuperAdminDashboardController extends Controller
{
    public function __construct(protected SuperAdminDashboardService $dashboard) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['staffRole.permissions'])->loadCount('passkeys');
        $recoveryRequests = $this->pendingRecoveryRequests();
        $matrix = $this->dashboard->permissionMatrix();
        $portalQuery = User::query()
            ->whereIn('role', [UserRole::Admin, UserRole::SuperAdmin, UserRole::Student])
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
            ->orderByRaw("FIELD(role, 'super_admin', 'admin', 'student')")
            ->orderBy('account_id');

        return view('admin.super-dashboard', [
            'user' => $user,
            'notificationsCount' => $recoveryRequests->count(),
            'recoveryRequests' => $recoveryRequests,
            'statistics' => $this->dashboard->statistics(),
            'systemHealth' => $this->dashboard->systemHealth(),
            'settings' => $this->dashboard->settings(),
            'staffRoles' => $matrix['roles'],
            'permissions' => $matrix['permissions'],
            'auditLogs' => AuditLog::query()->latest()->limit(25)->get(),
            'passkeys' => Passkey::query()->with('user')->latest()->limit(25)->get(),
            'elections' => Election::query()->withCount(['votes', 'candidates'])->latest()->limit(10)->get(),
            'portalUsers' => $portalQuery->paginate(25)->withQueryString(),
            'voterEligibility' => [
                'enrolled' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Enrolled)->count(),
                'probation' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Probation)->count(),
                'withdrawn' => User::query()->where('role', UserRole::Student)->where('student_status', StudentStatus::Withdrawn)->count(),
            ],
            'backups' => SystemBackup::query()->with('creator')->latest()->limit(10)->get(),
            'candidates' => Candidate::query()->with(['election', 'category'])->latest()->limit(10)->get(),
        ]);
    }

    protected function pendingRecoveryRequests()
    {
        return PasskeyRecoveryRequest::query()
            ->with('user')
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->latest()
            ->limit(50)
            ->get();
    }
}
