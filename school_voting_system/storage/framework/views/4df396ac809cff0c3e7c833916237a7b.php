<?php
    $hasPortal = (bool) ($portalAccountUrl ?? false);
    $isRegistered = (bool) $record->is_registered
        || (method_exists($record, 'isFullyRegistered') && $record->isFullyRegistered());
    $needsConfirmLinked = $isRegistered || $hasPortal;

    $confirmMessage = $hasPortal
        ? 'Remove this roster row only? The portal account is not deleted.'
        : ($isRegistered
            ? 'This ID is marked registered but has no portal account. Remove it so they can be added and register again?'
            : 'Remove this roster record?');
?>
<form method="POST" action="<?php echo e(route($routePrefix.'.destroy', $record)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($confirmMessage)->toHtml() ?>)">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
    <?php if($needsConfirmLinked): ?>
        <input type="hidden" name="confirm_linked" value="1">
    <?php endif; ?>
    <button type="submit" class="<?php echo e($buttonClass ?? 'rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10'); ?>">Remove</button>
</form>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/rosters/_remove-form.blade.php ENDPATH**/ ?>