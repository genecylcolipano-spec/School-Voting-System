<?php

namespace App\Jobs;

use App\Models\Announcement;
use App\Models\User;
use App\Services\Portal\AnnouncementService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FanOutAnnouncementNotificationsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $announcementId,
        public int $actorId,
        public bool $sendInApp,
        public bool $sendEmail,
    ) {}

    public function handle(AnnouncementService $announcements): void
    {
        $announcement = Announcement::query()->find($this->announcementId);
        $actor = User::query()->find($this->actorId);

        if (! $announcement || ! $actor || ! $announcement->isLive()) {
            return;
        }

        $announcements->deliverToRecipients(
            $announcement,
            $actor,
            $this->sendInApp,
            $this->sendEmail,
        );
    }
}
