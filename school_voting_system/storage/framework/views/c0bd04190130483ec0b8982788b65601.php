<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'action' => null, 'actionLabel' => null, 'description' => null, 'showAction' => true]));

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

foreach (array_filter((['title', 'action' => null, 'actionLabel' => null, 'description' => null, 'showAction' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-white"><?php echo e($title); ?></h2>
        <?php if($description): ?>
            <p class="mt-1 text-sm text-slate-400"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <?php if($action && $actionLabel && $showAction): ?>
        <a href="<?php echo e($action); ?>" class="rounded-xl bg-gradient-to-r from-violet-600 to-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:opacity-90">
            <?php echo e($actionLabel); ?>

        </a>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/admin/partials/page-header.blade.php ENDPATH**/ ?>