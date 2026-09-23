<?php
    $feed = collect($cards ?? [])
        ->sortBy([
            fn ($card) => empty($card['is_urgent']) ? 1 : 0,
            fn ($card) => ($card['last_vote_at'] ?? '—') === '—' ? 1 : 0,
        ])
        ->take(6)
        ->map(function (array $card) {
            $name = $card['name'] ?? 'Activity';
            $when = $card['last_vote_at'] ?? null;

            if (! empty($card['is_live'])) {
                return [
                    'tone' => 'live',
                    'text' => $when && $when !== '—'
                        ? "Vote activity in {$name} · {$when}"
                        : "{$name} is live — waiting for votes",
                ];
            }

            if (($card['status_key'] ?? '') === 'voting_paused') {
                return [
                    'tone' => 'paused',
                    'text' => "Voting paused · {$name}",
                ];
            }

            if (! empty($card['is_results_pending'])) {
                return [
                    'tone' => 'pending',
                    'text' => "Results pending · {$name}",
                ];
            }

            if (! empty($card['is_published'])) {
                return [
                    'tone' => 'published',
                    'text' => "Results published · {$name}",
                ];
            }

            if (($card['status_key'] ?? '') === 'registration_open') {
                return [
                    'tone' => 'info',
                    'text' => "Registration open · {$name}",
                ];
            }

            if (($card['status_key'] ?? '') === 'judging_open') {
                $done = (int) ($card['judges_completed'] ?? 0);
                $total = (int) ($card['judges_total'] ?? 0);

                return [
                    'tone' => 'info',
                    'text' => $total > 0
                        ? "Judging in progress · {$name} ({$done}/{$total} judges)"
                        : "Judging open · {$name}",
                ];
            }

            return [
                'tone' => 'idle',
                'text' => "{$name} · ".($card['status_label'] ?? 'Scheduled'),
            ];
        })
        ->values();
?>

<?php if($feed->isNotEmpty()): ?>
    <section class="mb-5 rounded-2xl border border-violet-500/15 bg-slate-900/70 p-4" data-activity-feed>
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recent activity</h3>
            <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-600">From current view</span>
        </div>
        <ul class="space-y-2">
            <?php $__currentLoopData = $feed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $dot = match ($item['tone']) {
                        'live' => 'bg-emerald-400',
                        'paused' => 'bg-amber-400',
                        'pending' => 'bg-orange-400',
                        'published' => 'bg-sky-400',
                        'info' => 'bg-violet-400',
                        default => 'bg-slate-500',
                    };
                ?>
                <li class="flex items-start gap-2.5 text-sm text-slate-300">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full <?php echo e($dot); ?>"></span>
                    <span class="min-w-0 leading-snug"><?php echo e($item['text']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_activity-feed.blade.php ENDPATH**/ ?>