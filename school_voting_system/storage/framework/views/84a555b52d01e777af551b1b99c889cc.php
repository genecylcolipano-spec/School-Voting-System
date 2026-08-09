<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['registerOptionsUrl', 'registerVerifyUrl']));

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

foreach (array_filter((['registerOptionsUrl', 'registerVerifyUrl']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'rounded-xl border border-cyan-200 bg-cyan-50/80 p-5'])); ?>>
    <p class="text-sm font-semibold text-cyan-950">Register a passkey on this device</p>
    <p class="mt-1 text-sm text-cyan-900/80">
        Enable one-tap fingerprint or face sign-in. You can register multiple devices with unique names.
    </p>

    <div class="mt-4">
        <label for="device_name" class="mb-1 block text-xs font-medium text-cyan-900">Device name</label>
        <input
            id="device_name"
            type="text"
            value="Primary Device"
            class="w-full rounded-lg border border-cyan-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-200"
            placeholder="e.g. School Laptop, iPhone"
        >
    </div>

    <button
        id="register-passkey-btn"
        type="button"
        data-options-url="<?php echo e($registerOptionsUrl); ?>"
        data-verify-url="<?php echo e($registerVerifyUrl); ?>"
        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-500 disabled:opacity-60"
    >
        <span id="register-passkey-label">Register passkey</span>
        <svg id="register-passkey-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
    </button>

    <p id="register-passkey-status" class="mt-2 text-xs text-cyan-900" aria-live="polite"></p>
</div>

<?php if (! $__env->hasRenderedOnce('3f237568-4293-4a62-adf8-f0ad018416ff')): $__env->markAsRenderedOnce('3f237568-4293-4a62-adf8-f0ad018416ff'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/passkey-register.js']); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\voting system\school_voting_system\resources\views/components/passkey-register.blade.php ENDPATH**/ ?>