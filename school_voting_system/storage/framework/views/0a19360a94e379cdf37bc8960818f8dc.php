<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'session',
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
    'session',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-3">
    <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
            <p class="text-sm font-semibold text-white">
                <?php echo e($session['device']); ?>

                <?php if($session['is_current']): ?>
                    <span class="ml-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-200">Current</span>
                <?php endif; ?>
            </p>
            <p class="mt-1 text-xs text-slate-500">
                <?php echo e($session['browser']); ?> · <?php echo e($session['os']); ?>

                <?php if($session['ip_address']): ?>
                    · IP <?php echo e($session['ip_address']); ?>

                <?php endif; ?>
            </p>
        </div>
        <p class="text-xs text-slate-500">
            Last activity <?php echo e(optional($session['last_activity'])->diffForHumans() ?? '—'); ?>

        </p>
    </div>
</li>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/settings/session-card.blade.php ENDPATH**/ ?>