<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'subtitle' => null,
    'gradient' => 'from-violet-600 to-indigo-500',
    'iconClass' => 'text-white',
    'shadowClass' => 'shadow-violet-900/40',
    'collapsedAware' => false,
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
    'subtitle' => null,
    'gradient' => 'from-violet-600 to-indigo-500',
    'iconClass' => 'text-white',
    'shadowClass' => 'shadow-violet-900/40',
    'collapsedAware' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $systemName = \App\Support\SchoolBranding::systemName();
    $schoolName = \App\Support\SchoolBranding::schoolName();
    $poweredBy = \App\Support\SchoolBranding::poweredBy();
    // Custom upload only — otherwise use the purple book icon (not the Rosemont crest).
    $logoUrl = \App\Support\SchoolBranding::logoUrl(withFallback: false);
?>

<div <?php echo e($attributes->class('flex shrink-0 items-center gap-3')); ?>>
    <?php if($logoUrl): ?>
        <img
            src="<?php echo e($logoUrl); ?>"
            alt="<?php echo e($schoolName); ?>"
            class="h-11 w-11 shrink-0 rounded-xl border border-white/10 object-cover shadow-lg <?php echo e($shadowClass); ?>"
        >
    <?php else: ?>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br <?php echo e($gradient); ?> <?php echo e($iconClass); ?> shadow-lg <?php echo e($shadowClass); ?>" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
    <?php endif; ?>

    <div <?php if($collapsedAware): ?> x-show="!collapsed" <?php endif; ?> class="min-w-0">
        <p class="truncate font-semibold text-white"><?php echo e($systemName); ?></p>
        <?php if(filled($subtitle)): ?>
            <p class="truncate text-xs text-slate-500"><?php echo e($subtitle); ?></p>
        <?php endif; ?>
        <?php if(filled($poweredBy)): ?>
            <p class="truncate text-[10px] text-slate-500"><?php echo e($poweredBy); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/school-brand.blade.php ENDPATH**/ ?>