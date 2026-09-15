<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'event',
    'locked' => false,
    'showOpensAt' => false,
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
    'event',
    'locked' => false,
    'showOpensAt' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $phase = $event->judgingPhase();
    $key = $locked ? 'submitted' : $phase['key'];
    $label = $locked ? 'Submitted' : $phase['label'];
    $tone = match ($key) {
        'open', 'submitted' => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-200',
        'scheduled' => 'border-sky-500/30 bg-sky-500/10 text-sky-200',
        default => 'border-amber-500/30 bg-amber-500/10 text-amber-200',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'shrink-0'])); ?>>
    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide <?php echo e($tone); ?>">
        <?php echo e($label); ?>

    </span>
    <?php if($showOpensAt && ! $locked && $phase['key'] === 'scheduled' && filled($phase['opens_at'])): ?>
        <p class="mt-1 text-xs text-sky-200/80">Opens <?php echo e($phase['opens_at']); ?></p>
    <?php elseif($showOpensAt && ! $locked && $phase['key'] === 'open' && filled($phase['closes_at'])): ?>
        <p class="mt-1 text-xs text-emerald-200/80">Closes <?php echo e($phase['closes_at']); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/faculty/judging-phase-badge.blade.php ENDPATH**/ ?>