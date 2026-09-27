<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['account']));

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

foreach (array_filter((['account']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mt-3 space-y-1.5 text-sm">
    <p class="text-slate-400">
        <span class="text-slate-500">Email</span>
        <?php if(filled($account->email)): ?>
            <a href="mailto:<?php echo e($account->email); ?>" class="ml-2 text-slate-200 hover:text-white"><?php echo e($account->email); ?></a>
        <?php else: ?>
            <span class="ml-2 text-slate-500">—</span>
        <?php endif; ?>
        <span class="text-slate-600"> · <?php echo e($account->roleLabel()); ?></span>
    </p>

    <p class="text-slate-400">
        <span class="text-slate-500">Phone</span>
        <?php if($account->hasContactPhone()): ?>
            <a href="<?php echo e($account->phoneTelHref()); ?>" class="ml-2 font-medium text-cyan-200 hover:text-cyan-100"><?php echo e($account->phone); ?></a>
        <?php else: ?>
            <span class="ml-2 text-slate-500">Not on file</span>
        <?php endif; ?>
    </p>
    <?php if($account->hasContactPhone()): ?>
        <p class="text-xs text-slate-500">If they cannot open email, copy the enrollment URL after Reset Passkey.</p>
    <?php else: ?>
        <p class="text-xs text-slate-500">They can add a number in Settings, or you can record one on Edit Information.</p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/profile-contact.blade.php ENDPATH**/ ?>