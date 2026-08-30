<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'registerOptionsUrl',
    'registerVerifyUrl',
    'theme' => 'light',
    'accent' => 'cyan',
    'compact' => false,
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
    'compact' => false,
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
    $isCompact = filter_var($compact, FILTER_VALIDATE_BOOLEAN);
?>

<div <?php echo e($attributes->merge(['class' => $isDark
    ? ($isTeal
        ? ($isCompact ? 'rounded-xl border border-teal-500/20 bg-slate-950/50 p-3' : 'rounded-xl border border-teal-500/20 bg-slate-950/50 p-5')
        : ($isCompact ? 'rounded-xl border border-cyan-500/20 bg-slate-950/50 p-3' : 'rounded-xl border border-cyan-500/20 bg-slate-950/50 p-5'))
    : 'rounded-xl border border-cyan-200 bg-cyan-50/80 p-5'])); ?>>
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-sm font-semibold', 'text-white' => $isDark, 'text-cyan-950' => ! $isDark]); ?>">
        Register a passkey on this device
    </p>
    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-1 text-sm', 'text-slate-400' => $isDark, 'text-cyan-900/80' => ! $isDark]); ?>">
        Extra passkeys are for other computers or phones. Each one should have its own name.
    </p>

    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-2.5' => $isCompact, 'mt-4' => ! $isCompact]); ?>">
        <label for="device_name" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mb-1 block text-xs font-medium', 'text-slate-300' => $isDark, 'text-cyan-900' => ! $isDark]); ?>">Device name</label>
        <input
            id="device_name"
            type="text"
            value=""
            autocapitalize="words"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'w-full rounded-lg px-3 text-sm focus:outline-none focus:ring-2',
                'py-1.5' => $isCompact,
                'py-2' => ! $isCompact,
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
            'inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60',
            'mt-2.5 py-2' => $isCompact,
            'mt-4 py-3' => ! $isCompact,
            'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950 hover:opacity-90' => $isTeal,
            'bg-cyan-500 text-slate-950 hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60' => $isDark && ! $isTeal,
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

<?php if (! $__env->hasRenderedOnce('4093ab1a-a37a-40b0-a0a6-5c9c73fc4c01')): $__env->markAsRenderedOnce('4093ab1a-a37a-40b0-a0a6-5c9c73fc4c01'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/passkey-register.js']); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/passkey-register.blade.php ENDPATH**/ ?>