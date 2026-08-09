<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
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
