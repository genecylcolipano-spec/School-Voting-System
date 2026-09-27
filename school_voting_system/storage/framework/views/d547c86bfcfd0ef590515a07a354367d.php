<article class="rs-card group flex h-full flex-col rounded-2xl border border-violet-500/15 bg-slate-900/80 p-5 shadow-sm shadow-black/20 transition hover:-translate-y-0.5 hover:border-violet-400/35">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-300"><?php echo e($event['category']); ?></p>
            <h3 class="mt-1 text-lg font-semibold text-white"><?php echo e($event['name']); ?></h3>
        </div>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'vm-badge shrink-0',
            'vm-badge--live' => ($event['voting_status_tone'] ?? '') === 'live',
            'vm-badge--paused' => ($event['voting_status_tone'] ?? '') === 'paused',
            'vm-badge--closed' => ($event['voting_status_tone'] ?? '') === 'closed',
            'vm-badge--idle' => ($event['voting_status_tone'] ?? '') === 'idle',
        ]); ?>"><?php echo e($event['voting_status']); ?></span>
    </div>

    <dl class="mt-4 space-y-3 text-xs text-slate-400">
        <div class="flex items-center justify-between gap-3">
            <dt class="uppercase tracking-wide text-slate-500">Total Votes</dt>
            <dd class="text-base font-bold text-white"><?php echo e(number_format($event['total_votes'])); ?></dd>
        </div>
        <div class="flex items-center justify-between gap-3">
            <dt class="uppercase tracking-wide text-slate-500">Date</dt>
            <dd class="font-medium text-slate-200"><?php echo e($event['display_date'] ?? '—'); ?></dd>
        </div>
    </dl>

    <div class="mt-auto flex flex-wrap gap-2 pt-5">
        <a href="<?php echo e($event['show_url']); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'rounded-xl px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90',
            'bg-gradient-to-r from-amber-500 to-orange-500' => $event['is_live'] ?? false,
            'bg-gradient-to-r from-violet-600 to-indigo-500' => ! ($event['is_live'] ?? false),
        ]); ?>">
            <?php echo e($event['view_label']); ?>

        </a>
        <?php if(($canExport ?? false) && $event['total_votes'] > 0): ?>
            <a href="<?php echo e($event['export_url']); ?>" class="rounded-xl border border-violet-500/30 px-4 py-2 text-sm font-semibold text-violet-200 transition hover:bg-violet-500/10">
                Export
            </a>
        <?php endif; ?>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/results/_event-card.blade.php ENDPATH**/ ?>