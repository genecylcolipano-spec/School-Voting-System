<?php
    $match = $recoveryRequest->queue_match ?? 'unknown';
    $queueUser = $recoveryRequest->queue_user;
    $lastSentAt = $recoveryRequest->last_sent_at;
    $cooldownRemaining = $lastSentAt ? max(0, 120 - $lastSentAt->diffInSeconds(now())) : 0;
    $cooldownActive = $cooldownRemaining > 0;
?>

<?php if($queueUser): ?>
    <a href="<?php echo e($recoveryRequest->queue_user_url); ?>" class="font-medium text-white hover:text-violet-200"><?php echo e($queueUser->name); ?></a>
    <?php if($match === 'exact'): ?>
        <p class="mt-0.5 text-xs text-emerald-300">Exact match</p>
    <?php else: ?>
        <p class="mt-0.5 text-xs text-amber-300">Account found · email does not match</p>
        <p class="mt-0.5 break-all text-xs text-slate-400">On file: <?php echo e($recoveryRequest->queue_on_file_email); ?></p>
    <?php endif; ?>
<?php else: ?>
    <span class="text-slate-300">No such account</span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/passkey-recovery-match.blade.php ENDPATH**/ ?>