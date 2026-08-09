<?php

namespace App\Services\SuperAdmin;

use App\Enums\AuditActionType;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Carbon;

class MaintenanceModeService
{
    public function __construct(protected AuditLogService $audit) {}

    public function isEnabled(): bool
    {
        return (bool) SystemSetting::getValue('maintenance_mode_enabled', false);
    }

    public function allowsSuperAdmin(): bool
    {
        return (bool) SystemSetting::getValue('maintenance_allow_super_admin', true);
    }

    public function message(): string
    {
        return (string) SystemSetting::getValue(
            'maintenance_message',
            'The system is temporarily unavailable due to scheduled maintenance.'
        );
    }

    public function estimatedReturnAt(): ?Carbon
    {
        $value = SystemSetting::getValue('maintenance_return_at');

        return filled($value) ? Carbon::parse((string) $value) : null;
    }

    /**
     * @return array{
     *     enabled: bool,
     *     message: string,
     *     return_at: ?Carbon,
     *     allow_super_admin: bool,
     *     updated_at: ?Carbon,
     *     updated_by: ?User
     * }
     */
    public function status(): array
    {
        $updatedById = SystemSetting::getValue('maintenance_updated_by');
        $updatedAt = SystemSetting::getValue('maintenance_updated_at');

        return [
            'enabled' => $this->isEnabled(),
            'message' => $this->message(),
            'return_at' => $this->estimatedReturnAt(),
            'allow_super_admin' => $this->allowsSuperAdmin(),
            'updated_at' => filled($updatedAt) ? Carbon::parse((string) $updatedAt) : null,
            'updated_by' => $updatedById
                ? User::query()->find($updatedById)
                : null,
        ];
    }

    public function enable(User $actor, array $data): void
    {
        SystemSetting::setValue('maintenance_mode_enabled', true, 'boolean');
        SystemSetting::setValue(
            'maintenance_message',
            $data['message'] ?: 'The system is temporarily unavailable due to scheduled maintenance.',
        );
        SystemSetting::setValue(
            'maintenance_return_at',
            filled($data['return_at'] ?? null) ? (string) $data['return_at'] : '',
        );
        SystemSetting::setValue('maintenance_allow_super_admin', (bool) ($data['allow_super_admin'] ?? true), 'boolean');
        $this->touchMeta($actor);

        $this->audit->record($actor, 'Activated Maintenance Mode', AuditActionType::System);

        app(\App\Services\Portal\PortalNotificationService::class)->maintenanceEnabled($actor);
    }

    public function disable(User $actor): void
    {
        SystemSetting::setValue('maintenance_mode_enabled', false, 'boolean');
        $this->touchMeta($actor);

        $this->audit->record($actor, 'Disabled Maintenance Mode', AuditActionType::System);

        app(\App\Services\Portal\PortalNotificationService::class)->maintenanceDisabled($actor);
    }

    public function updateMessage(User $actor, array $data): void
    {
        SystemSetting::setValue(
            'maintenance_message',
            $data['message'] ?: 'The system is temporarily unavailable due to scheduled maintenance.',
        );
        SystemSetting::setValue(
            'maintenance_return_at',
            filled($data['return_at'] ?? null) ? (string) $data['return_at'] : '',
        );
        SystemSetting::setValue('maintenance_allow_super_admin', (bool) ($data['allow_super_admin'] ?? true), 'boolean');
        $this->touchMeta($actor);

        $this->audit->record($actor, 'Updated Maintenance Mode settings', AuditActionType::System);
    }

    public function userMayBypass(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isSuperAdmin() && $this->allowsSuperAdmin();
    }

    protected function touchMeta(User $actor): void
    {
        SystemSetting::setValue('maintenance_updated_by', $actor->id, 'integer');
        SystemSetting::setValue('maintenance_updated_at', now()->toIso8601String());
    }
}
