<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title', 'description' => null, 'badge' => null, 'badgeTone' => 'amber']));

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

foreach (array_filter((['title', 'description' => null, 'badge' => null, 'badgeTone' => 'amber']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $badgeTones = [
        'amber' => 'bg-amber-500/20 text-amber-200',
        'emerald' => 'bg-emerald-500/20 text-emerald-200',
        'violet' => 'bg-violet-500/20 text-violet-200',
    ];
?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-3'])); ?>>
    <div>
        <h3 class="text-lg font-semibold text-white"><?php echo e($title); ?></h3>
        <?php if($description): ?>
            <p class="mt-1 text-sm text-slate-400"><?php echo e($description); ?></p>
        <?php endif; ?>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <?php if($badge): ?>
            <span data-live-fundraiser-badge class="rounded-full px-3 py-1 text-xs font-semibold <?php echo e($badgeTones[$badgeTone]); ?> <?php echo e($badge ? '' : 'hidden'); ?>"><?php echo e($badge); ?></span>
        <?php else: ?>
            <span data-live-fundraiser-badge class="hidden rounded-full px-3 py-1 text-xs font-semibold <?php echo e($badgeTones[$badgeTone]); ?>"></span>
        <?php endif; ?>
        <?php if(isset($actions)): ?>
            <div class="flex flex-wrap gap-2"><?php echo e($actions); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin-section-header.blade.php ENDPATH**/ ?>