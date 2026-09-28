<?php
    $steps = $steps ?? [];
?>

<?php if(! empty($steps)): ?>
    <ol class="mt-3 flex flex-wrap items-center gap-x-1 gap-y-1.5" data-phase-steps aria-label="Workflow progress">
        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $state = $step['state'] ?? 'upcoming';
                $dot = match ($state) {
                    'done' => 'bg-violet-400',
                    'live' => 'bg-emerald-400 ring-2 ring-emerald-400/30',
                    'current' => 'bg-amber-400 ring-2 ring-amber-400/25',
                    default => 'bg-slate-600',
                };
                $text = match ($state) {
                    'done' => 'text-violet-300/90',
                    'live' => 'text-emerald-300 font-bold',
                    'current' => 'text-amber-200 font-semibold',
                    default => 'text-slate-500',
                };
            ?>
            <li class="inline-flex items-center gap-1 <?php echo e($text); ?>">
                <span class="h-1.5 w-1.5 shrink-0 rounded-full <?php echo e($dot); ?>"></span>
                <span class="text-[10px] uppercase tracking-wide"><?php echo e($step['label']); ?></span>
                <?php if (! ($loop->last)): ?>
                    <span class="mx-1 h-px w-3 bg-slate-700/80" aria-hidden="true"></span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_phase-timeline.blade.php ENDPATH**/ ?>