<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'event',
    'bare' => false,
    'showWarning' => true,
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
    'bare' => false,
    'showWarning' => true,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $needsContain = $event->detailBannerNeedsContainLayout();
?>

<div <?php echo e($attributes); ?>>
    <?php if (isset($component)) { $__componentOriginalb4ae95e62e8615350ae7fdaa410354d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.event-image','data' => ['bare' => $bare,'src' => $event->detailBannerUrl(),'srcMedium' => $event->detailBannerMediumUrl(),'srcMobile' => $event->detailBannerMobileUrl(),'orientation' => $event->detailBannerOrientation(),'contain' => $needsContain,'alt' => $event->title]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('event-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['bare' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($bare),'src' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->detailBannerUrl()),'src-medium' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->detailBannerMediumUrl()),'src-mobile' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->detailBannerMobileUrl()),'orientation' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->detailBannerOrientation()),'contain' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($needsContain),'alt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event->title)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0)): ?>
<?php $attributes = $__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0; ?>
<?php unset($__attributesOriginalb4ae95e62e8615350ae7fdaa410354d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb4ae95e62e8615350ae7fdaa410354d0)): ?>
<?php $component = $__componentOriginalb4ae95e62e8615350ae7fdaa410354d0; ?>
<?php unset($__componentOriginalb4ae95e62e8615350ae7fdaa410354d0); ?>
<?php endif; ?>
    <?php if($showWarning && $event->shouldWarnNonLandscapeBanner()): ?>
        <p class="mt-2 rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-center text-xs font-medium text-amber-100 sm:text-sm">
            This image is portrait. For best appearance, upload a landscape banner (1600 × 900).
        </p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/competition-detail-banner.blade.php ENDPATH**/ ?>