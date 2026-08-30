<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['passkey', 'currentPasskeyId' => 0, 'stacked' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['passkey', 'currentPasskeyId' => 0, 'stacked' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $owner = $passkey->user;
    $usable = $passkey->isUsable();
    $isCurrent = $usable && (int) $currentPasskeyId > 0 && (int) $passkey->id === (int) $currentPasskeyId;
    $accountLabel = $owner ? $owner->name.' ('.$owner->account_id.')' : 'this account';
    $revokeConfirm = $isCurrent
        ? 'This is the passkey you are signed in with. Disable it? This session stays open until you sign out, but this device cannot log in again. This does not send a new enrollment link.'
        : 'Revoke this passkey for '.$accountLabel.'? They will not be able to sign in with this device. This does not send a new enrollment link.';
    $lostConfirm = $isCurrent
        ? 'This is the passkey you are signed in with. Mark it lost? This session stays open until you sign out, but this device cannot log in again. This does not send a new enrollment link.'
        : 'Mark this passkey as lost for '.$accountLabel.'? Same effect as revoke — the device cannot sign in. This does not send a new enrollment link.';
    $revokeClass = $stacked
        ? 'rounded-lg border border-rose-500/30 px-3 py-1.5 text-xs font-semibold text-rose-300 hover:bg-rose-500/10'
        : 'text-xs font-semibold text-rose-300 hover:text-rose-200';
    $lostClass = $stacked
        ? 'rounded-lg border border-amber-500/30 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/10'
        : 'text-xs font-semibold text-amber-300 hover:text-amber-200';
?>

<?php if($usable): ?>
    <div class="<?php echo e($stacked ? 'flex flex-wrap gap-2' : 'flex flex-col items-start gap-1'); ?>">
        <form method="POST" action="<?php echo e(route('super-admin.passkeys.action', $passkey)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($revokeConfirm)->toHtml() ?>);">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="revoke">
            <button type="submit" class="<?php echo e($revokeClass); ?>">Revoke</button>
        </form>
        <form method="POST" action="<?php echo e(route('super-admin.passkeys.action', $passkey)); ?>" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from($lostConfirm)->toHtml() ?>);">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="lost">
            <button type="submit" class="<?php echo e($lostClass); ?>">Mark lost</button>
        </form>
    </div>
<?php else: ?>
    <span class="text-xs text-slate-500">Disabled</span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/passkey-row-actions.blade.php ENDPATH**/ ?>