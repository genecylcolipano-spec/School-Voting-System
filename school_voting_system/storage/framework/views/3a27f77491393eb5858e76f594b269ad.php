<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'description' => null,
    'borderClass' => 'border-cyan-500/15',
]));

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

foreach (array_filter(([
    'title',
    'description' => null,
    'borderClass' => 'border-cyan-500/15',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => "rounded-2xl border {$borderClass} bg-slate-900/70 p-5 sm:p-6"])); ?>>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-white"><?php echo e($title); ?></h2>
            <?php if($description): ?>
                <p class="mt-1 text-sm text-slate-400"><?php echo e($description); ?></p>
            <?php endif; ?>
        </div>
        <?php if(isset($actions)): ?>
            <div class="shrink-0"><?php echo e($actions); ?></div>
        <?php endif; ?>
    </div>
    <div class="mt-5">
        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/settings/security-card.blade.php ENDPATH**/ ?>