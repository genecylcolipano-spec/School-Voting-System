<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'passkey',
    'accent' => 'cyan',
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
    'passkey',
    'accent' => 'cyan',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $label = $passkey->display_name ?? $passkey->device_name ?? $passkey->name ?? 'Device';
    $renameClass = $accent === 'teal' ? 'text-teal-300 hover:text-teal-200' : 'text-cyan-300 hover:text-cyan-200';
?>

<li class="rounded-xl border border-slate-800 bg-slate-950/40 px-4 py-4" data-device-id="<?php echo e($passkey->id); ?>">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <p class="font-medium text-white"><?php echo e($label); ?></p>
                <?php if($passkey->is_current ?? false): ?>
                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-200">Current Device</span>
                <?php endif; ?>
            </div>
            <dl class="mt-3 grid gap-1 text-xs text-slate-500 sm:grid-cols-2">
                <div><dt class="inline text-slate-600">Type:</dt> <dd class="inline text-slate-300"><?php echo e($passkey->device_type ?? '—'); ?></dd></div>
                <div><dt class="inline text-slate-600">Browser:</dt> <dd class="inline text-slate-300"><?php echo e($passkey->browser ?? '—'); ?></dd></div>
                <div><dt class="inline text-slate-600">OS:</dt> <dd class="inline text-slate-300"><?php echo e($passkey->operating_system ?? '—'); ?></dd></div>
                <div><dt class="inline text-slate-600">Registered:</dt> <dd class="inline text-slate-300"><?php echo e(optional($passkey->created_at)->format('M d, Y') ?? '—'); ?></dd></div>
                <div class="sm:col-span-2"><dt class="inline text-slate-600">Last used:</dt> <dd class="inline text-slate-300"><?php echo e(optional($passkey->last_used_at)->diffForHumans() ?? 'Never'); ?></dd></div>
            </dl>
        </div>
        <div class="flex shrink-0 gap-3">
            <button type="button" data-rename="<?php echo e($passkey->id); ?>" data-name="<?php echo e($label); ?>" class="text-xs font-semibold <?php echo e($renameClass); ?>">Rename</button>
            <button type="button" data-remove="<?php echo e($passkey->id); ?>" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Remove</button>
        </div>
    </div>
</li>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/settings/device-card.blade.php ENDPATH**/ ?>