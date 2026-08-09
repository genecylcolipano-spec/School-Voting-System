<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url',
    'alt',
    'portrait' => false,
    'contain' => null,
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
    'url',
    'alt',
    'portrait' => false,
    'contain' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Portrait/square: full image + blurred fill. Landscape: cover the 16:9 frame.
    $useContain = $contain ?? $portrait;
?>

<div <?php echo e($attributes->class(['absolute inset-0'])); ?>>
    <?php if($useContain): ?>
        <img
            src="<?php echo e($url); ?>"
            alt=""
            aria-hidden="true"
            loading="lazy"
            class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.35] saturate-125"
        >
        <img
            src="<?php echo e($url); ?>"
            alt="<?php echo e($alt); ?>"
            loading="lazy"
            class="absolute inset-0 z-[1] h-full w-full object-contain object-center"
        >
    <?php else: ?>
        <img
            src="<?php echo e($url); ?>"
            alt="<?php echo e($alt); ?>"
            loading="lazy"
            class="h-full w-full object-cover object-center"
        >
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/student/campaigns/_banner-media.blade.php ENDPATH**/ ?>