<?php

namespace App\Console\Commands;

use App\Enums\ElectionStatus;
use App\Enums\UserRole;
use App\Models\Election;
use App\Models\Event;
use App\Models\User;
use App\Services\Portal\PortalNotificationService;
use App\Services\SuperAdmin\ElectionLifecycleService;
use Illuminate\Console\Command;

class ProcessScheduledElectionsCommand extends Command
{
    protected $signature = 'portal:process-scheduled-elections';

    protected $description = 'Open or close elections whose scheduled or voting window times have elapsed';

    public function handle(ElectionLifecycleService $lifecycle, PortalNotificationService $notifications): int
    {
        $registrationNotices = $notifications->dispatchTalentRegistrationOpenedNotices();

        if ($registrationNotices > 0) {
            $this->line("Talent registration notices sent: {$registrationNotices}.");
        }

        $syncedEvents = Event::markOverdueAsCompleted();

        if ($syncedEvents > 0) {
            $this->line("Synced {$syncedEvents} school event status(es).");
        }

        $actor = User::query()
            ->where('role', UserRole::SuperAdmin)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $actor) {
            $this->warn('No active Super Admin found; skipping scheduled election processing.');

            return self::FAILURE;
        }

        $opened = 0;
        $closed = 0;
        $now = now();

        Election::query()
            ->whereNull('annulled_at')
            ->where('status', ElectionStatus::Draft)
            ->where(function ($query) use ($now) {
                $query->where(function ($scheduled) use ($now) {
                    $scheduled->whereNotNull('scheduled_open_at')
                        ->where('scheduled_open_at', '<=', $now);
                })->orWhere(function ($window) use ($now) {
                    $window->whereNull('scheduled_open_at')
                        ->whereNotNull('voting_starts_at')
                        ->where('voting_starts_at', '<=', $now);
                });
            })
            ->orderBy('id')
            ->each(function (Election $election) use ($lifecycle, $actor, &$opened) {
                $lifecycle->open($election, $actor);
                $opened++;
                $this->line("Opened: {$election->title}");
            });

        Election::query()
            ->whereNull('annulled_at')
            ->where('status', ElectionStatus::Active)
            ->where(function ($query) use ($now) {
                $query->where(function ($scheduled) use ($now) {
                    $scheduled->whereNotNull('scheduled_close_at')
                        ->where('scheduled_close_at', '<=', $now);
                })->orWhere(function ($window) use ($now) {
                    $window->whereNull('scheduled_close_at')
                        ->whereNotNull('voting_ends_at')
                        ->where('voting_ends_at', '<=', $now);
                });
            })
            ->orderBy('id')
            ->each(function (Election $election) use ($lifecycle, $actor, &$closed) {
                $lifecycle->close($election, $actor);
                $closed++;
                $this->line("Closed: {$election->title}");
            });

        $this->info("Scheduled elections processed. Opened: {$opened}. Closed: {$closed}.");

        return self::SUCCESS;
    }
}
