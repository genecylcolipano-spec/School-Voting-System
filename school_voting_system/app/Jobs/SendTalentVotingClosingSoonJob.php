<?php

namespace App\Jobs;

use App\Services\Portal\PortalNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTalentVotingClosingSoonJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $withinHours = 24) {}

    public function handle(PortalNotificationService $notifications): void
    {
        $notifications->dispatchTalentVotingClosingSoonReminders($this->withinHours);
    }
}
