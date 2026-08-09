<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'label' => null]));

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

foreach (array_filter((['status', 'label' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusValue = match (true) {
        $status instanceof \BackedEnum => $status->value,
        $status instanceof \UnitEnum => $status->name,
        default => (string) $status,
    };
    $normalized = strtolower($statusValue);
    $display = $label ?? ucfirst(str_replace('_', ' ', $normalized));
    $classes = match (true) {
        in_array($normalized, ['pending', 'open', 'entries_open', 'scheduled', 'registration_open', 'registration_period', 'results_pending', 'upcoming']) => 'border-amber-500/30 bg-amber-500/15 text-amber-300',
        in_array($normalized, ['approved', 'verified', 'active', 'voting_open', 'resolved', 'success', 'ongoing']) => 'border-emerald-500/30 bg-emerald-500/15 text-emerald-300',
        in_array($normalized, ['draft', 'registration_closed', 'voting_closed', 'voting_paused']) => 'border-slate-500/30 bg-slate-600/40 text-slate-300',
        in_array($normalized, ['archived', 'inactive', 'completed']) => 'border-slate-500/25 bg-slate-600/40 text-slate-400',
        in_array($normalized, ['rejected', 'failed', 'annulled']) => 'border-rose-500/30 bg-rose-500/15 text-rose-300',
        in_array($normalized, ['results_published', 'published']) => 'border-violet-500/30 bg-violet-500/15 text-violet-300',
        default => 'border-slate-600/40 bg-slate-700 text-slate-300',
    };
?>

<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide '.$classes])); ?>>
    <?php echo e($display); ?>

</span>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/admin-status-badge.blade.php ENDPATH**/ ?>