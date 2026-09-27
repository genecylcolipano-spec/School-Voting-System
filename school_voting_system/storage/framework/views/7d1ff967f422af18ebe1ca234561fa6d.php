<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'href',
    'label',
    'active' => false,
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
    'href',
    'label',
    'active' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<a
    href="<?php echo e($href); ?>"
    @click="sidebarOpen = false"
    <?php echo e($attributes->merge([
        'class' => 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition '.(
            $active
                ? 'bg-gradient-to-r from-violet-600 to-indigo-500 text-white shadow-lg shadow-violet-900/30'
                : 'text-slate-400 hover:bg-slate-800/70 hover:text-white'
        ),
    ])); ?>

>
    <?php echo e($icon); ?>

    <span x-show="!collapsed" class="truncate"><?php echo e($label); ?></span>
</a>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/portal-sidebar-link.blade.php ENDPATH**/ ?>