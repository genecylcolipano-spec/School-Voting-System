@props(['registerOptionsUrl', 'registerVerifyUrl'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-cyan-200 bg-cyan-50/80 p-5']) }}>
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
        data-options-url="{{ $registerOptionsUrl }}"
        data-verify-url="{{ $registerVerifyUrl }}"
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

@once
    @vite(['resources/js/passkey-register.js'])
@endonce
