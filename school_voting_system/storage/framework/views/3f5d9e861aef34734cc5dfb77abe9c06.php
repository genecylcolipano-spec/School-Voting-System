<?php
    $percent = max(0, min(100, (float) ($percent ?? 0)));
    $isWinner = (int) ($rank ?? 0) === 1 && $percent > 0;
    $isTeal = ($accent ?? 'cyan') === 'teal';
    $barClass = $isWinner
        ? ($isTeal ? 'bg-gradient-to-r from-teal-500 to-emerald-400' : 'bg-gradient-to-r from-cyan-500 to-emerald-400')
        : ($isTeal ? 'bg-gradient-to-r from-teal-500 to-emerald-400' : 'bg-gradient-to-r from-cyan-500 to-sky-400');
    $valueClass = $isTeal ? 'text-teal-300' : 'text-cyan-300';
    $voteDecimals = isset($votes) && floor((float) $votes) != (float) $votes ? 2 : 0;
?>

<li class="space-y-2 px-5 py-3.5">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <span class="text-sm text-slate-400">#<?php echo e($rank); ?></span>
            <span class="ml-2 font-medium text-white"><?php echo e($name); ?></span>
        </div>
        <?php if(! empty($meta)): ?>
            <span class="shrink-0 text-xs text-slate-500"><?php echo e($meta); ?></span>
        <?php endif; ?>
    </div>

    <?php if(isset($votes)): ?>
        <div class="flex items-center gap-3">
            <div
                class="h-2 min-w-0 flex-1 overflow-hidden rounded-full bg-slate-800"
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="<?php echo e(number_format($percent, 1, '.', '')); ?>"
                aria-label="<?php echo e($name); ?> received <?php echo e(number_format($percent, 1)); ?> percent of votes"
            >
                <div class="h-full rounded-full <?php echo e($barClass); ?>" style="width: <?php echo e(number_format($percent, 1, '.', '')); ?>%"></div>
            </div>
            <span class="w-24 shrink-0 text-right text-xs font-semibold <?php echo e($valueClass); ?>">
                <?php echo e(number_format((float) $votes, $voteDecimals)); ?> · <?php echo e(number_format($percent, 1)); ?>%
            </span>
        </div>
    <?php endif; ?>
</li>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/results/_ranking-row.blade.php ENDPATH**/ ?>