<?php

namespace App\Console\Commands;

use App\Enums\ElectionStatus;
use App\Enums\UserRole;
use App\Models\Election;
use App\Models\User;
use App\Services\SuperAdmin\ElectionLifecycleService;
use Illuminate\Console\Command;

class ProcessScheduledElectionsCommand extends Command
{
    protected $signature = 'portal:process-scheduled-elections';

    protected $description = 'Open or close elections whose scheduled_open_at / scheduled_close_at have elapsed';

    public function handle(ElectionLifecycleService $lifecycle): int
    {
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
            ->whereNotNull('scheduled_open_at')
            ->where('scheduled_open_at', '<=', $now)
            ->whereNull('annulled_at')
            ->whereIn('status', [ElectionStatus::Draft])
            ->orderBy('id')
            ->each(function (Election $election) use ($lifecycle, $actor, &$opened) {
                $lifecycle->open($election, $actor);
                $opened++;
                $this->line("Opened: {$election->title}");
            });

        Election::query()
            ->whereNotNull('scheduled_close_at')
            ->where('scheduled_close_at', '<=', $now)
            ->whereNull('annulled_at')
            ->where('status', ElectionStatus::Active)
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
