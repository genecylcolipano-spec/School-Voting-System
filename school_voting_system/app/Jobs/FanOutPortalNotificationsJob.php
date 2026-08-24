<?php

namespace App\Jobs;

use App\Enums\NotificationModule;
use App\Enums\UserRole;
use App\Services\Portal\PortalNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Legacy queued fan-out. Live delivery now inserts in-request so bells appear
 * without queue:work. This job still processes any leftover queued rows.
 */
class FanOutPortalNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public string $role,
        public string $title,
        public string $message,
        public string $type,
        public ?string $module = null,
        public ?int $relatedId = null,
        public ?int $authorId = null,
    ) {}

    public function handle(PortalNotificationService $notifications): void
    {
        $role = UserRole::from($this->role);
        $module = $this->module !== null
            ? NotificationModule::tryFrom($this->module)
            : null;

        $notifications->insertFanOutForRole(
            $role,
            $this->title,
            $this->message,
            $this->type,
            $this->authorId,
            $module,
            $this->relatedId,
        );
    }
}
