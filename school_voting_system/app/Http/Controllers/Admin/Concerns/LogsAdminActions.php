<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Enums\AuditActionType;
use App\Models\PasskeyRecoveryRequest;
use App\Services\SuperAdmin\AuditLogService;

trait LogsAdminActions
{
    protected function audit(): AuditLogService
    {
        return app(AuditLogService::class);
    }

    protected function logAdminAction(
        string $message,
        AuditActionType $type = AuditActionType::Election,
        ?string $targetType = null,
        ?int $targetId = null,
        array $metadata = [],
    ): void {
        $this->audit()->record(
            request()->user(),
            $message,
            $type,
            targetType: $targetType,
            targetId: $targetId,
            metadata: $metadata,
        );
    }

    protected function recoveryCount(): int
    {
        return PasskeyRecoveryRequest::query()
            ->where('status', PasskeyRecoveryRequest::STATUS_PENDING)
            ->count();
    }
}
