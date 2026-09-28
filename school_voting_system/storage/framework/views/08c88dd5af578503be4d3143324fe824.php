<?php
    $showLeaders = ! empty($card['show_position_leaders']);
    $leaders = collect($card['position_leaders'] ?? []);
?>

<div
    class="overflow-hidden rounded-xl border border-violet-500/15 bg-slate-950/40 <?php echo e($showLeaders ? '' : 'hidden'); ?>"
    data-position-leaders
>
    <div class="border-b border-slate-800 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Leading by position</div>
    <ul class="<?php echo e($leaders->isEmpty() ? 'hidden' : ''); ?> divide-y divide-slate-800" data-position-leaders-list>
        <?php $__currentLoopData = $leaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-center gap-3 px-3 py-2 text-sm">
                <span class="w-28 shrink-0 truncate text-[11px] font-semibold uppercase tracking-wide text-violet-300"><?php echo e($row['position']); ?></span>
                <span class="min-w-0 flex-1 truncate text-slate-200">
                    <?php echo e($row['display']); ?>

                    <?php if(! empty($row['tied'])): ?>
                        <span class="ml-1 text-[10px] font-semibold uppercase tracking-wide text-amber-300">Tie</span>
                    <?php endif; ?>
                </span>
                <span class="font-bold text-white"><?php echo e(number_format($row['votes'] ?? 0)); ?></span>
                <span class="w-12 text-right text-xs text-slate-400"><?php echo e($row['percent'] ?? 0); ?>%</span>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <p class="<?php echo e($leaders->isEmpty() ? '' : 'hidden'); ?> px-3 py-4 text-sm text-slate-400" data-position-leaders-empty>No candidates yet.</p>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_position-leaders.blade.php ENDPATH**/ ?>