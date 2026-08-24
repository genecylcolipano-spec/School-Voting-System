<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'registerOptionsUrl',
    'registerVerifyUrl',
    'theme' => 'light',
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
    'registerOptionsUrl',
    'registerVerifyUrl',
    'theme' => 'light',
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
    $isDark = $theme === 'dark';
    $isTeal = $isDark && $accent === 'teal';
?>

<div <?php echo e($attributes->merge(['class' => $isDark
    ? ($isTeal ? 'rounded-xl border border-teal-500/20 bg-slate-950/50 p-5' : 'rounded-xl border border-cyan-500/20 bg-slate-950/50 p-5')
    : 'rounded-xl border border-cyan-200 bg-cyan-50/80 p-5'])); ?>>
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-sm font-semibold', 'text-white' => $isDark, 'text-cyan-950' => ! $isDark]); ?>">
        Register a passkey on this device
    </p>
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-1 text-sm', 'text-slate-400' => $isDark, 'text-cyan-900/80' => ! $isDark]); ?>">
        This computer can only be enrolled once. Additional passkeys are for other devices, each with its own name.
    </p>

    <div class="mt-4">
        <label for="device_name" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mb-1 block text-xs font-medium', 'text-slate-300' => $isDark, 'text-cyan-900' => ! $isDark]); ?>">Device name</label>
        <input
            id="device_name"
            type="text"
            value="Primary Device"
            autocapitalize="words"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'w-full rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2',
                'border border-slate-700 bg-slate-950 text-slate-100 focus:border-teal-400/50 focus:ring-teal-500/20' => $isTeal,
                'border border-slate-700 bg-slate-950 text-slate-100 focus:border-cyan-400/50 focus:ring-cyan-500/20' => $isDark && ! $isTeal,
                'border border-cyan-200 bg-white text-slate-900 focus:border-cyan-400 focus:ring-cyan-200' => ! $isDark,
            ]); ?>"
            placeholder="e.g. School Laptop, iPhone"
        >
    </div>

    <button
        id="register-passkey-btn"
        type="button"
        data-options-url="<?php echo e($registerOptionsUrl); ?>"
        data-verify-url="<?php echo e($registerVerifyUrl); ?>"
        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition disabled:opacity-60',
            'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950 hover:opacity-90' => $isTeal,
            'bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 hover:opacity-90' => $isDark && ! $isTeal,
            'bg-cyan-600 text-white hover:bg-cyan-500' => ! $isDark,
        ]); ?>"
    >
        <span id="register-passkey-label">Register passkey</span>
        <svg id="register-passkey-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
    </button>

    <p id="register-passkey-status" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-2 text-xs', 'text-slate-400' => $isDark, 'text-cyan-900' => ! $isDark]); ?>" aria-live="polite"></p>
</div>

<?php if (! $__env->hasRenderedOnce('dabb2789-0532-45c1-ac80-54bbe33b2f24')): $__env->markAsRenderedOnce('dabb2789-0532-45c1-ac80-54bbe33b2f24'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/passkey-register.js']); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/passkey-register.blade.php ENDPATH**/ ?>