<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'size' => 'md', // sm | md | lg | xl
    'rounded' => 'rounded-xl',
    'withBorder' => true,
    'withFallback' => true,
    'alt' => null,
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
    'size' => 'md', // sm | md | lg | xl
    'rounded' => 'rounded-xl',
    'withBorder' => true,
    'withFallback' => true,
    'alt' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $logoUrl = \App\Support\SchoolBranding::logoUrl(withFallback: (bool) $withFallback);
    $alt ??= \App\Support\SchoolBranding::schoolName();

    $box = match ($size) {
        'sm' => 'h-11 w-11',
        'lg' => 'h-20 w-20',
        'xl' => 'h-24 w-24',
        default => 'h-16 w-16',
    };

    // Intrinsic pixel hint ≈ 2× CSS size for retina sharpness (display size stays CSS-driven).
    $px = match ($size) {
        'sm' => 88,
        'lg' => 160,
        'xl' => 192,
        default => 128,
    };

    $frame = trim(implode(' ', array_filter([
        'school-logo',
        $box,
        $rounded,
        $withBorder ? 'border border-white/10' : null,
        'bg-slate-950/40 shadow-lg',
    ])));
?>

<?php if($logoUrl): ?>
    <span <?php echo e($attributes->class($frame)); ?>>
        <img
            src="<?php echo e($logoUrl); ?>"
            alt="<?php echo e($alt); ?>"
            width="<?php echo e($px); ?>"
            height="<?php echo e($px); ?>"
            loading="eager"
            decoding="async"
        >
    </span>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/school-logo.blade.php ENDPATH**/ ?>