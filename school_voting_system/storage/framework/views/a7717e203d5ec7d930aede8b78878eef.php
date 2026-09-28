<?php
    $lastSentAt = $recoveryRequest->last_sent_at;
    $cooldownRemaining = $lastSentAt ? max(0, 120 - $lastSentAt->diffInSeconds(now())) : 0;
    $cooldownActive = $cooldownRemaining > 0;
    $stacked = $stacked ?? false;
?>

<div class="<?php echo e($stacked ? 'flex flex-col gap-2' : 'flex flex-wrap items-center gap-2'); ?>">
    <?php if($recoveryRequest->queue_can_issue): ?>
        <button
            type="button"
            class="<?php echo e($stacked ? 'w-full' : ''); ?> rounded-lg bg-gradient-to-r from-cyan-500 to-sky-400 px-3 py-2 text-xs font-semibold text-slate-950 hover:opacity-90 disabled:opacity-50"
            data-enroll-url="<?php echo e($recoveryRequest->queue_enroll_url); ?>"
            data-recovery-request-id="<?php echo e($recoveryRequest->id); ?>"
            data-confirm="<?php echo e($recoveryRequest->queue_confirm); ?>"
            <?php if($cooldownActive): echo 'disabled'; endif; ?>
        >
            Generate enrollment link
        </button>
        <?php if($cooldownActive): ?>
            <p class="text-xs text-amber-300">Cooldown: retry in <?php echo e($cooldownRemaining); ?>s</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($recoveryRequest->queue_can_dismiss): ?>
        <button
            type="button"
            class="<?php echo e($stacked ? 'w-full' : ''); ?> rounded-lg border border-slate-700 px-3 py-2 text-xs font-semibold text-slate-300 hover:border-rose-500/40 hover:text-rose-200"
            data-dismiss-url="<?php echo e($recoveryRequest->queue_dismiss_url); ?>"
            data-recovery-request-id="<?php echo e($recoveryRequest->id); ?>"
        >
            Dismiss
        </button>
    <?php elseif(! $recoveryRequest->queue_can_issue): ?>
        <span class="text-xs text-amber-300">Needs manual verification</span>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/passkey-recovery-actions.blade.php ENDPATH**/ ?>