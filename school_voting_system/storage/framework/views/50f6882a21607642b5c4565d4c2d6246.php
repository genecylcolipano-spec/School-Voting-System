<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'src',
    'alt' => '',
    /** When true, render only the fill layer (for absolute hero overlays). */
    'bare' => false,
    /** Force contain+blur layout. Auto-detected when orientation is provided. */
    'contain' => null,
    'orientation' => null,
    'srcMedium' => null,
    'srcMobile' => null,
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
    'src',
    'alt' => '',
    /** When true, render only the fill layer (for absolute hero overlays). */
    'bare' => false,
    /** Force contain+blur layout. Auto-detected when orientation is provided. */
    'contain' => null,
    'orientation' => null,
    'srcMedium' => null,
    'srcMobile' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $placeholder = \App\Support\EventImageUrl::placeholder();
    $useContain = $contain;
    if ($useContain === null && $orientation) {
        $useContain = in_array($orientation, ['portrait', 'square'], true);
    }
    $useContain = (bool) $useContain;

    $desktopSrc = $src ?: $placeholder;
    $mediumSrc = $srcMedium ?: $desktopSrc;
    $mobileSrc = $srcMobile ?: $mediumSrc;
?>

<?php if($bare): ?>
    <div <?php echo e($attributes->class(['absolute inset-0 overflow-hidden'])); ?>>
        <?php if($useContain): ?>
            <img
                src="<?php echo e($desktopSrc); ?>"
                alt=""
                aria-hidden="true"
                class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125"
                loading="lazy"
            >
            <picture class="absolute inset-0 z-[1]">
                <source media="(max-width: 640px)" srcset="<?php echo e($mobileSrc); ?>">
                <source media="(max-width: 1024px)" srcset="<?php echo e($mediumSrc); ?>">
                <img
                    src="<?php echo e($desktopSrc); ?>"
                    alt="<?php echo e($alt); ?>"
                    class="h-full w-full object-contain object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='<?php echo e($placeholder); ?>';"
                >
            </picture>
        <?php else: ?>
            <picture class="absolute inset-0">
                <source media="(max-width: 640px)" srcset="<?php echo e($mobileSrc); ?>">
                <source media="(max-width: 1024px)" srcset="<?php echo e($mediumSrc); ?>">
                <img
                    src="<?php echo e($desktopSrc); ?>"
                    alt="<?php echo e($alt); ?>"
                    class="h-full w-full object-cover object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='<?php echo e($placeholder); ?>';"
                >
            </picture>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div <?php echo e($attributes->class(['relative aspect-video w-full overflow-hidden bg-slate-950'])); ?>>
        <?php if($useContain): ?>
            <img
                src="<?php echo e($desktopSrc); ?>"
                alt=""
                aria-hidden="true"
                class="absolute inset-0 h-full w-full scale-110 object-cover object-center blur-2xl brightness-[0.4] saturate-125"
                loading="lazy"
            >
            <picture class="absolute inset-0 z-[1]">
                <source media="(max-width: 640px)" srcset="<?php echo e($mobileSrc); ?>">
                <source media="(max-width: 1024px)" srcset="<?php echo e($mediumSrc); ?>">
                <img
                    src="<?php echo e($desktopSrc); ?>"
                    alt="<?php echo e($alt); ?>"
                    class="h-full w-full object-contain object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='<?php echo e($placeholder); ?>';"
                >
            </picture>
        <?php else: ?>
            <picture class="absolute inset-0">
                <source media="(max-width: 640px)" srcset="<?php echo e($mobileSrc); ?>">
                <source media="(max-width: 1024px)" srcset="<?php echo e($mediumSrc); ?>">
                <img
                    src="<?php echo e($desktopSrc); ?>"
                    alt="<?php echo e($alt); ?>"
                    class="h-full w-full object-cover object-center"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='<?php echo e($placeholder); ?>';"
                >
            </picture>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/event-image.blade.php ENDPATH**/ ?>