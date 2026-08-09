<?php

namespace App\Services\Talent;

use App\Models\TalentEvent;
use App\Models\User;

class TalentEventPublishingService
{
    public function publish(TalentEvent $event, User $admin): TalentEvent
    {
        if ($event->approvedEntries()->count() === 0) {
            return $event;
        }

        $event->forceFill([
            'published_to_students' => true,
            'published_at' => $event->published_at ?? now(),
        ])->save();

        return $event->fresh();
    }

    public function publishIfReady(TalentEvent $event, User $admin): void
    {
        if ($event->approvedEntries()->count() > 0) {
            $this->publish($event, $admin);
        }
    }
}
