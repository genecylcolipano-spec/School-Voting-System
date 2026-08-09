<?php

namespace App\Console\Commands;

use App\Models\PortalNotification;
use Illuminate\Console\Command;

class PrunePortalNotificationsCommand extends Command
{
    protected $signature = 'portal:prune-notifications
                            {--days=90 : Delete read notifications older than this many days}
                            {--unread-days=180 : Delete unread notifications older than this many days}
                            {--dry-run : Report counts without deleting}';

    protected $description = 'Prune old portal notifications to keep the inbox table bounded';

    public function handle(): int
    {
        $readDays = max(1, (int) $this->option('days'));
        $unreadDays = max($readDays, (int) $this->option('unread-days'));
        $dryRun = (bool) $this->option('dry-run');

        $readCutoff = now()->subDays($readDays);
        $unreadCutoff = now()->subDays($unreadDays);

        $readQuery = PortalNotification::query()
            ->whereNotNull('read_at')
            ->where('created_at', '<', $readCutoff);

        $unreadQuery = PortalNotification::query()
            ->whereNull('read_at')
            ->where('created_at', '<', $unreadCutoff);

        $readCount = (clone $readQuery)->count();
        $unreadCount = (clone $unreadQuery)->count();

        if ($dryRun) {
            $this->info("Dry run: would delete {$readCount} read (>{$readDays}d) and {$unreadCount} unread (>{$unreadDays}d).");

            return self::SUCCESS;
        }

        $deletedRead = $readQuery->delete();
        $deletedUnread = $unreadQuery->delete();

        $this->info("Pruned {$deletedRead} read and {$deletedUnread} unread portal notifications.");

        return self::SUCCESS;
    }
}
