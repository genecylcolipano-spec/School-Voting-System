<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     from?: string|null,
     *     to?: string|null,
     *     module?: string|null,
     *     action_type?: string|null,
     *     role?: string|null,
     *     user_id?: int|string|null
     * }  $filters
     */
    public function filteredQuery(array $filters = []): Builder
    {
        $search = filled($filters['search'] ?? null) ? trim((string) $filters['search']) : null;
        $from = filled($filters['from'] ?? null) ? (string) $filters['from'] : null;
        $to = filled($filters['to'] ?? null) ? (string) $filters['to'] : null;
        $module = filled($filters['module'] ?? null)
            ? (string) $filters['module']
            : (filled($filters['action_type'] ?? null) ? (string) $filters['action_type'] : null);
        $role = filled($filters['role'] ?? null) ? (string) $filters['role'] : null;
        $userId = (int) ($filters['user_id'] ?? 0) ?: null;

        return AuditLog::query()
            ->with('user:id,name,account_id,role')
            ->when($search, function (Builder $query) use ($search) {
                $term = '%'.$search.'%';
                $query->where(function (Builder $query) use ($term) {
                    $query->where('action', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', $term)->orWhere('account_id', 'like', $term));
                });
            })
            ->when($from, fn (Builder $query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('created_at', '<=', $to))
            ->when($module, fn (Builder $query) => $query->where('action_type', $module))
            ->when($role, fn (Builder $query) => $query->whereHas('user', fn (Builder $q) => $q->where('role', $role)))
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->latest();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function exportCsv(array $filters = [], int $limit = 5000): string
    {
        $logs = $this->filteredQuery($filters)->limit($limit)->get();

        $csv = "Timestamp,Admin,Role,Action,Type,IP,Device,Status\n";
        foreach ($logs as $log) {
            $csv .= implode(',', [
                '"'.$log->created_at?->toDateTimeString().'"',
                '"'.str_replace('"', '""', $log->admin_name).'"',
                '"'.str_replace('"', '""', $log->admin_role ?? '').'"',
                '"'.str_replace('"', '""', $log->action).'"',
                '"'.($log->action_type?->value ?? '').'"',
                '"'.($log->ip_address ?? '').'"',
                '"'.str_replace('"', '""', $log->device_name ?? '').'"',
                '"'.$log->status.'"',
            ])."\n";
        }

        return $csv;
    }

    public function record(
        User $actor,
        string $action,
        AuditActionType $type = AuditActionType::System,
        string $status = 'success',
        ?string $targetType = null,
        ?int $targetId = null,
        ?array $metadata = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::query()->create([
            'user_id' => $actor->id,
            'admin_name' => $actor->name,
            'admin_role' => $actor->staffRole?->name ?? $actor->role?->value,
            'action' => $action,
            'action_type' => $type,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => (string) $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'device_name' => $request->header('X-Device-Name'),
            'status' => $status,
        ]);
    }
}
