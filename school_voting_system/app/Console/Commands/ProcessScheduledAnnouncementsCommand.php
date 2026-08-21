<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\User;
use App\Services\Portal\AnnouncementService;
use Illuminate\Console\Command;

class ProcessScheduledAnnouncementsCommand extends Command
{
    protected $signature = 'portal:process-scheduled-announcements';

    protected $description = 'Send notifications for announcements whose publish time has elapsed';

    public function handle(AnnouncementService $announcements): int
    {
        $now = now();
        $sent = 0;

        Announcement::query()
            ->where('is_published', true)
            ->where('notifications_sent_count', 0)
            ->where(function ($query) {
                $query->where('notify_in_app', true)
                    ->orWhere('send_email', true);
            })
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $now)
            ->orderBy('id')
            ->each(function (Announcement $announcement) use ($announcements, &$sent) {
                if (! $announcement->isLive()) {
                    return;
                }

                $actor = $announcement->author
                    ?? User::query()->whereKey($announcement->created_by)->first();

                if (! $actor) {
                    return;
                }

                $count = $announcements->dispatchNotificationsIfNeeded($announcement, $actor, false);

                if ($count > 0) {
                    $sent++;
                    $this->line("Notified: {$announcement->title}");
                }
            });

        $this->info("Scheduled announcements processed. Sent: {$sent}.");

        return self::SUCCESS;
    }
}
