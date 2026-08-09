<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminActivityTimelineService
{
    public function __construct(protected AdminScopeService $scope) {}

    /**
     * @return Collection<int, array{icon: string, activity: string, user: string, date: string, time: string, at: string}>
     */
    public function recentForDashboard(User $admin, int $limit = 20): Collection
    {
        return $this->scopedQuery($admin)
            ->limit($limit)
            ->get()
            ->map(fn (AuditLog $log) => $this->formatEntry($log));
    }

    protected function scopedQuery(User $admin): \Illuminate\Database\Eloquent\Builder
    {
        $query = AuditLog::query()->latest();

        if ($admin->isSuperAdmin()) {
            return $query;
        }

        $election = $this->scope->assignedElection($admin);

        return $query->where(function ($inner) use ($admin, $election) {
            $inner->where('user_id', $admin->id);

            if ($election) {
                $inner->orWhere(function ($scoped) use ($election) {
                    $scoped->where('target_type', 'election')
                        ->where('target_id', $election->id);
                })->orWhere('action', 'like', '%'.$election->title.'%');
            }
        });
    }

    /**
     * @return array{icon: string, activity: string, user: string, date: string, time: string, at: string}
     */
    protected function formatEntry(AuditLog $log): array
    {
        $action = (string) $log->action;

        return [
            'icon' => $this->iconForAction($action),
            'activity' => $action,
            'user' => $log->admin_name ?? $log->user?->name ?? 'System',
            'date' => $log->created_at?->format('M d, Y') ?? '—',
            'time' => $log->created_at?->format('g:i A') ?? '—',
            'at' => $log->created_at?->toIso8601String() ?? '',
            'module' => ucfirst($log->action_type?->value ?? 'system'),
            'role' => $log->admin_role ?? '—',
        ];
    }

    protected function iconForAction(string $action): string
    {
        $normalized = strtolower($action);

        return match (true) {
            str_contains($normalized, 'created election'), str_contains($normalized, 'election created') => '🗳',
            str_contains($normalized, 'created candidate'), str_contains($normalized, 'candidate') && str_contains($normalized, 'created') => '👤',
            str_contains($normalized, 'opened election'), str_contains($normalized, 'voting open'), str_contains($normalized, 'open voting') => '🟢',
            str_contains($normalized, 'paused'), str_contains($normalized, 'pause') => '⏸',
            str_contains($normalized, 'resumed'), str_contains($normalized, 'resume') => '▶',
            str_contains($normalized, 'closed election'), str_contains($normalized, 'voting closed'), str_contains($normalized, 'closed') => '🔒',
            str_contains($normalized, 'published results'), str_contains($normalized, 'results published') => '🏆',
            str_contains($normalized, 'unpublished') => '📋',
            str_contains($normalized, 'created event'), str_contains($normalized, 'event created') => '📅',
            str_contains($normalized, 'talent') && str_contains($normalized, 'created') => '🎭',
            str_contains($normalized, 'login') => '🔑',
            str_contains($normalized, 'logout') => '🚪',
            default => '📌',
        };
    }
}
