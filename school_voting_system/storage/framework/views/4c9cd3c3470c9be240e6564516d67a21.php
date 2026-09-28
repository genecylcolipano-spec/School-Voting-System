<?php
    $countdown = $card['countdown'] ?? null;
    $hasCountdown = is_array($countdown)
        && filled($countdown['target_at_iso'] ?? null)
        && empty($countdown['is_closed']);
?>

<div
    class="flex items-center justify-between gap-3 rounded-xl border border-cyan-500/15 bg-cyan-500/5 px-3 py-2 <?php echo e($hasCountdown ? '' : 'hidden'); ?>"
    data-countdown
    data-target-iso="<?php echo e($countdown['target_at_iso'] ?? ''); ?>"
    data-countdown-phase="<?php echo e($countdown['phase'] ?? ''); ?>"
>
    <p class="text-[10px] font-semibold uppercase tracking-wide text-cyan-300/90" data-countdown-label><?php echo e($countdown['label'] ?? 'Time remaining'); ?></p>
    <p class="text-sm font-bold tabular-nums text-white" data-countdown-remaining><?php echo e($countdown['remaining'] ?? '—'); ?></p>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/live-monitoring/_countdown.blade.php ENDPATH**/ ?>