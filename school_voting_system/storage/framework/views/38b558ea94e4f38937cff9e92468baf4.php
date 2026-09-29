<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Account Summary',
    'borderClass' => 'border-cyan-500/15',
    'rows' => [],
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
    'title' => 'Account Summary',
    'borderClass' => 'border-cyan-500/15',
    'rows' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => "rounded-2xl border {$borderClass} bg-slate-900/70 p-5 sm:p-6"])); ?>>
    <h2 class="text-lg font-semibold text-white"><?php echo e($title); ?></h2>
    <dl class="mt-4 space-y-3 text-sm">
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'flex justify-between gap-3',
                'border-b border-slate-800 pb-2' => ! $loop->last,
            ]); ?>">
                <dt class="text-slate-500"><?php echo e($row['label']); ?></dt>
                <dd class="<?php echo \Illuminate\Support\Arr::toCssClasses(['font-medium text-right', $row['valueClass'] ?? 'text-slate-200']); ?>">
                    <?php echo e($row['value']); ?>

                </dd>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </dl>
    <?php echo e($slot); ?>

</section>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/settings/account-summary.blade.php ENDPATH**/ ?>