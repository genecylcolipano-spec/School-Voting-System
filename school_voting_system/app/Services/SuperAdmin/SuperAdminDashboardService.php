<?php

namespace App\Services\SuperAdmin;

use App\Enums\ElectionStatus;
use App\Enums\FundraiserStatus;
use App\Enums\PasskeyStatus;
use App\Enums\StudentStatus;
use App\Enums\TalentEventStatus;
use App\Enums\UserRole;
use App\Models\Election;
use App\Models\Fundraiser;
use App\Models\Passkey;
use App\Models\PasskeyRecoveryRequest;
use App\Models\Permission;
use App\Models\StaffRole;
use App\Models\SystemBackup;
use App\Models\SystemSetting;
use App\Models\TalentEvent;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        $liveElections = Election::query()
            ->where('status', ElectionStatus::Active)
            ->where('is_paused', false)
            ->whereNull('annulled_at')
            ->orderByDesc('id')
            ->get(['id', 'title']);

        $liveIds = $liveElections->pluck('id');
        $voteQuery = Vote::query();
        if ($liveIds->isEmpty()) {
            $voteQuery->whereRaw('0 = 1');
        } else {
            $voteQuery->whereIn('election_id', $liveIds);
        }

        $votedStudents = (clone $voteQuery)->distinct('user_id')->count('user_id');
        $totalVotes = (clone $voteQuery)->count();
        $electionScope = match ($liveElections->count()) {
            0 => 'No live election',
            1 => (string) $liveElections->first()?->title,
            default => $liveElections->count().' live elections',
        };

        return [
            'students' => User::query()->where('role', UserRole::Student)->count(),
            'faculty' => User::query()->where('role', UserRole::Faculty)->count(),
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
            'total_votes' => $totalVotes,
            'voter_turnout' => $eligibleStudents > 0
                ? round(($votedStudents / $eligibleStudents) * 100, 1)
                : 0.0,
            'eligible_students' => $eligibleStudents,
            'voted_students' => $votedStudents,
            'election_scope' => $electionScope,
            'has_live_election' => $liveElections->isNotEmpty(),
        ];
    }

    /**
     * @param  array{status?: string, q?: string, role?: string}  $filters
     */
    public function paginatedPasskeys(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? 'active';
        if (! in_array($status, ['active', 'revoked', 'lost', 'all'], true)) {
            $status = 'active';
        }

        $search = trim((string) ($filters['q'] ?? ''));
        $role = trim((string) ($filters['role'] ?? ''));

        $query = Passkey::query()->with('user');

        if ($status !== 'all') {
            $query->where('status', match ($status) {
                'revoked' => PasskeyStatus::Revoked,
                'lost' => PasskeyStatus::Lost,
                default => PasskeyStatus::Active,
            });
        }

        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->whereHas('user', function ($userQuery) use ($term) {
                $userQuery->where('account_id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        if (in_array($role, ['student', 'admin', 'faculty', 'super_admin'], true)) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('role', $role));
        }

        return $query->latest()->paginate(15, ['*'], 'passkeys')->withQueryString();
    }

    public function liveElection(): ?Election
    {
        return Election::query()
            ->where('status', ElectionStatus::Active)
            ->where('is_paused', false)
            ->whereNull('annulled_at')
            ->latest('id')
            ->first();
    }

    public function activitySnapshot(): array
    {
        $openCompetitionStatuses = [
            TalentEventStatus::Scheduled,
            TalentEventStatus::EntriesOpen,
            TalentEventStatus::VotingOpen,
        ];

        return [
            'live_election' => $this->liveElection(),
            'competitions' => TalentEvent::query()
                ->withCount('votes')
                ->whereIn('status', $openCompetitionStatuses)
                ->latest('id')
                ->limit(3)
                ->get(),
            'competitions_open' => TalentEvent::query()
                ->whereIn('status', $openCompetitionStatuses)
                ->count(),
            'fundraisers' => Fundraiser::query()
                ->where('status', FundraiserStatus::Active)
                ->latest('id')
                ->limit(3)
                ->get(),
            'fundraisers_active' => Fundraiser::query()
                ->where('status', FundraiserStatus::Active)
                ->count(),
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
        $lastError = $this->recentLogError();

        $overall = 'Healthy';
        if (! $dbOk) {
            $overall = 'Degraded';
        } elseif (! $lastBackup || $lastError) {
            $overall = 'Attention';
        }

        return [
            'database' => ['status' => $dbOk ? 'ok' : 'error', 'message' => $dbMessage],
            'passkey_service' => ['status' => 'ok', 'message' => "{$passkeyCount} credentials registered"],
            'backup' => [
                'status' => $lastBackup ? 'ok' : 'warning',
                'message' => $lastBackup
                    ? 'Last backup '.$lastBackup->completed_at?->diffForHumans()
                    : 'No backups yet',
            ],
            'last_error' => [
                'status' => $lastError ? 'error' : 'ok',
                'message' => $lastError ?? 'No recent errors',
            ],
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

    public function recentLogErrorFrom(string $logPath, int $withinMinutes = 15): ?string
    {
        if (! File::exists($logPath)) {
            return null;
        }

        $cutoff = now()->subMinutes($withinMinutes);

        foreach (array_reverse($this->tailLogLines($logPath, 200)) as $line) {
            if (! preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.ERROR:/', $line, $matches)) {
                continue;
            }

            if ($matches[2] === 'testing') {
                continue;
            }

            $loggedAt = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
            if ($loggedAt === false || $loggedAt->lt($cutoff)) {
                return null;
            }

            return Str::limit($line, 120);
        }

        return null;
    }

    protected function recentLogError(): ?string
    {
        if (app()->environment('testing')) {
            return null;
        }

        return $this->recentLogErrorFrom(storage_path('logs/laravel.log'));
    }

    /**
     * @return list<string>
     */
    protected function tailLogLines(string $path, int $maxLines): array
    {
        $size = File::size($path);
        if ($size === 0) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $read = min($size, 64 * 1024);
        fseek($handle, -$read, SEEK_END);
        $chunk = stream_get_contents($handle) ?: '';
        fclose($handle);

        $lines = preg_split("/\r\n|\n|\r/", $chunk) ?: [];

        return array_values(array_slice($lines, -$maxLines));
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
