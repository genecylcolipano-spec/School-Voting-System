<?php

namespace App\Services\SuperAdmin;

use App\Enums\ElectionStatus;
use App\Enums\PasskeyStatus;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\Permission;
use App\Models\StaffRole;
use App\Models\SystemBackup;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SuperAdminDashboardService
{
    public function statistics(): array
    {
        $eligibleStudents = User::query()
            ->where('role', UserRole::Student)
            ->where('is_active', true)
            ->where('student_status', StudentStatus::Enrolled)
            ->count();

        $votedStudents = Vote::query()->distinct('user_id')->count('user_id');

        return [
            'students' => User::query()->where('role', UserRole::Student)->count(),
            'admins' => User::query()->where('role', UserRole::Admin)->count(),
            'super_admins' => User::query()->where('role', UserRole::SuperAdmin)->count(),
            'passkeys' => Passkey::query()->where('status', PasskeyStatus::Active)->count(),
            'pending_recoveries' => PasskeyRecoveryRequest::query()
                ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
                ->count(),
            'active_elections' => Election::query()
                ->where('status', ElectionStatus::Active)
                ->where('is_paused', false)
                ->whereNull('annulled_at')
                ->count(),
            'total_votes' => Vote::query()->count(),
            'voter_turnout' => $eligibleStudents > 0
                ? round(($votedStudents / $eligibleStudents) * 100, 1)
                : 0.0,
            'eligible_students' => $eligibleStudents,
            'voted_students' => $votedStudents,
        ];
    }

    public function systemHealth(): array
    {
        $dbOk = false;
        $dbMessage = 'Disconnected';

        try {
            DB::connection()->getPdo();
            $dbOk = true;
            $dbMessage = 'Connected';
        } catch (\Throwable) {
            $dbMessage = 'Connection failed';
        }

        $passkeyCount = Passkey::query()->count();
        $lastBackup = SystemBackup::query()->latest('completed_at')->first();
        $lastError = $this->lastLogError();

        $overall = ($dbOk && $passkeyCount >= 0) ? 'Healthy' : 'Degraded';

        return [
            'database' => ['status' => $dbOk ? 'ok' : 'error', 'message' => $dbMessage],
            'passkey_service' => ['status' => 'ok', 'message' => "{$passkeyCount} credentials registered"],
            'backup' => [
                'status' => $lastBackup ? 'ok' : 'warning',
                'message' => $lastBackup
                    ? 'Last backup '.$lastBackup->completed_at?->diffForHumans()
                    : 'No backups yet',
            ],
            'last_error' => $lastError,
            'overall' => $overall,
        ];
    }

    public function settings(): array
    {
        return [
            'session_timeout_minutes' => SystemSetting::getValue('session_timeout_minutes', 30),
            'ip_whitelist_enabled' => SystemSetting::getValue('ip_whitelist_enabled', false),
            'ip_whitelist' => SystemSetting::getValue('ip_whitelist', []),
            'two_factor_recovery_enabled' => SystemSetting::getValue('two_factor_recovery_enabled', true),
            'public_results_published' => SystemSetting::getValue('public_results_published', false),
            'support_email' => SystemSetting::getValue('support_email', config('mail.from.address', 'ictsupport@school.edu')),
            'support_team_label' => SystemSetting::getValue('support_team_label', 'ICT Support Team'),
        ];
    }

    protected function lastLogError(): ?string
    {
        $logPath = storage_path('logs/laravel.log');

        if (! File::exists($logPath)) {
            return null;
        }

        $lines = array_slice(file($logPath, FILE_IGNORE_NEW_LINES) ?: [], -200);

        foreach (array_reverse($lines) as $line) {
            if (str_contains($line, '.ERROR:')) {
                return Str::limit($line, 120);
            }
        }

        return null;
    }

    public function permissionMatrix(): array
    {
        if (! Schema::hasTable('staff_roles')) {
            return ['roles' => collect(), 'permissions' => collect()];
        }

        return [
            'roles' => StaffRole::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('category')->orderBy('label')->get(),
        ];
    }
}
