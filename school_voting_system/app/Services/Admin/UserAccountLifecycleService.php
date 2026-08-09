<?php

namespace App\Services\Admin;

use App\Enums\ElectionStatus;
use App\Enums\FundraiserStatus;
use App\Enums\TalentEventStatus;
use App\Enums\TalentJudgeScoreStatus;
use App\Enums\UserRole;
use App\Models\AdminAssignment;
use App\Models\AuditLog;
use App\Models\Fundraiser;
use App\Models\Passkey;
use App\Models\TalentEvent;
use App\Models\TalentEventJudge;
use App\Models\TalentJudgeScoreSheet;
use App\Models\User;
use App\Support\UserAgentParser;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class UserAccountLifecycleService
{
    /**
     * @return list<string>
     */
    public function adminRemovalBlockers(User $admin): array
    {
        $blockers = [];

        $activeElectionIds = AdminAssignment::query()
            ->where('user_id', $admin->id)
            ->whereHas('election', fn ($q) => $q->where('status', ElectionStatus::Active))
            ->with('election:id,title')
            ->get()
            ->pluck('election.title')
            ->filter()
            ->unique()
            ->values();

        if ($activeElectionIds->isNotEmpty()) {
            $blockers[] = 'Assigned to active election(s): '.$activeElectionIds->implode(', ');
        }

        $activeCompetitions = TalentEvent::query()
            ->where('created_by', $admin->id)
            ->whereNull('deleted_at')
            ->whereNotIn('status', [TalentEventStatus::Completed, TalentEventStatus::ResultsPublished])
            ->orderBy('title')
            ->limit(10)
            ->pluck('title');

        if ($activeCompetitions->isNotEmpty()) {
            $blockers[] = 'Owns active competition(s): '.$activeCompetitions->implode(', ');
        }

        $activeFundraisers = Fundraiser::query()
            ->where('created_by', $admin->id)
            ->whereIn('status', [FundraiserStatus::Active, FundraiserStatus::Scheduled, FundraiserStatus::GoalReached])
            ->orderBy('title')
            ->limit(10)
            ->pluck('title');

        if ($activeFundraisers->isNotEmpty()) {
            $blockers[] = 'Owns active fundraising campaign(s): '.$activeFundraisers->implode(', ');
        }

        return $blockers;
    }

    /**
     * @return list<string>
     */
    public function facultyRemovalBlockers(User $faculty): array
    {
        $blockers = [];

        $activeAssignments = TalentEventJudge::query()
            ->where('user_id', $faculty->id)
            ->whereHas('talentEvent', function ($q) {
                $q->whereNull('deleted_at')
                    ->whereNull('results_published_at')
                    ->whereNotIn('status', [
                        TalentEventStatus::Completed,
                        TalentEventStatus::ResultsPublished,
                    ]);
            })
            ->with('talentEvent:id,title')
            ->get()
            ->pluck('talentEvent.title')
            ->filter()
            ->unique()
            ->values();

        if ($activeAssignments->isNotEmpty()) {
            $blockers[] = 'Has active judging assignment(s): '.$activeAssignments->implode(', ');
        }

        $draftSheets = TalentJudgeScoreSheet::query()
            ->where('user_id', $faculty->id)
            ->where('status', TalentJudgeScoreStatus::Draft)
            ->count();

        if ($draftSheets > 0) {
            $blockers[] = "Has {$draftSheets} unfinished draft score sheet(s). Reassign or complete them first.";
        }

        $awaitingPublication = TalentJudgeScoreSheet::query()
            ->where('user_id', $faculty->id)
            ->where('status', TalentJudgeScoreStatus::Submitted)
            ->whereHas('talentEvent', function ($q) {
                $q->whereNull('results_published_at')
                    ->whereNotIn('status', [
                        TalentEventStatus::Completed,
                        TalentEventStatus::ResultsPublished,
                    ]);
            })
            ->count();

        if ($awaitingPublication > 0) {
            $blockers[] = "Has {$awaitingPublication} submitted score sheet(s) awaiting result publication.";
        }

        return $blockers;
    }

    /**
     * @return list<string>
     */
    public function removalBlockers(User $user): array
    {
        return match ($user->role) {
            UserRole::Admin => $this->adminRemovalBlockers($user),
            UserRole::Faculty => $this->facultyRemovalBlockers($user),
            UserRole::Student => ['Students cannot be permanently deleted. Archive or deactivate instead.'],
            default => ['This account cannot be removed from User Management.'],
        };
    }

    public function assertCanRemove(User $user): void
    {
        $blockers = $this->removalBlockers($user);

        if ($blockers !== []) {
            throw ValidationException::withMessages([
                'user' => array_merge(
                    ['This account cannot be removed yet. Deactivate or archive instead until dependencies are cleared.'],
                    $blockers,
                ),
            ]);
        }
    }

    public function archive(User $user): void
    {
        $user->forceFill([
            'archived_at' => now(),
            'is_active' => false,
        ])->save();
    }

    public function restore(User $user): void
    {
        $user->forceFill([
            'archived_at' => null,
            'is_active' => true,
        ])->save();
    }

    public function isArchived(User $user): bool
    {
        return $user->archived_at !== null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function loginHistoryFor(User $user, int $limit = 20): array
    {
        return AuditLog::query()
            ->where('user_id', $user->id)
            ->where('action_type', \App\Enums\AuditActionType::Auth)
            ->where('action', 'like', '%login%')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function (AuditLog $log) {
                $parsed = UserAgentParser::parse($log->user_agent);

                return [
                    'occurred_at' => $log->created_at,
                    'browser' => $parsed['browser'],
                    'os' => $parsed['os'],
                    'device' => $parsed['device'],
                    'ip_address' => $log->ip_address,
                    'status' => $log->status === 'success' ? 'Successful' : ucfirst((string) $log->status),
                ];
            })
            ->all();
    }

    /**
     * @return Collection<int, Passkey>
     */
    public function devicesFor(User $user): Collection
    {
        return Passkey::query()
            ->where('user_id', $user->id)
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return array{total: int, active: int, inactive: int, archived: int, devices: int}
     */
    public function roleSummary(UserRole $role): array
    {
        $base = User::query()->where('role', $role);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->whereNull('archived_at')->count(),
            'inactive' => (clone $base)->where('is_active', false)->whereNull('archived_at')->count(),
            'archived' => (clone $base)->whereNotNull('archived_at')->count(),
            'devices' => Passkey::query()
                ->whereIn('user_id', User::query()->where('role', $role)->select('id'))
                ->count(),
        ];
    }
}
