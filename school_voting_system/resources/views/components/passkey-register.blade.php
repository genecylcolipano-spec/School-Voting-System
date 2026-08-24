@props([
    'registerOptionsUrl',
    'registerVerifyUrl',
    'theme' => 'light',
    'accent' => 'cyan',
])

@php
    $isDark = $theme === 'dark';
    $isTeal = $isDark && $accent === 'teal';
@endphp

<div {{ $attributes->merge(['class' => $isDark
    ? ($isTeal ? 'rounded-xl border border-teal-500/20 bg-slate-950/50 p-5' : 'rounded-xl border border-cyan-500/20 bg-slate-950/50 p-5')
    : 'rounded-xl border border-cyan-200 bg-cyan-50/80 p-5']) }}>
    <p @class(['text-sm font-semibold', 'text-white' => $isDark, 'text-cyan-950' => ! $isDark])>
        Register a passkey on this device
    </p>
    <p @class(['mt-1 text-sm', 'text-slate-400' => $isDark, 'text-cyan-900/80' => ! $isDark])>
        This computer can only be enrolled once. Additional passkeys are for other devices, each with its own name.
    </p>

    <div class="mt-4">
        <label for="device_name" @class(['mb-1 block text-xs font-medium', 'text-slate-300' => $isDark, 'text-cyan-900' => ! $isDark])>Device name</label>
        <input
            id="device_name"
            type="text"
            value="Primary Device"
            autocapitalize="words"
            @class([
                'w-full rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2',
                'border border-slate-700 bg-slate-950 text-slate-100 focus:border-teal-400/50 focus:ring-teal-500/20' => $isTeal,
                'border border-slate-700 bg-slate-950 text-slate-100 focus:border-cyan-400/50 focus:ring-cyan-500/20' => $isDark && ! $isTeal,
                'border border-cyan-200 bg-white text-slate-900 focus:border-cyan-400 focus:ring-cyan-200' => ! $isDark,
            ])
            placeholder="e.g. School Laptop, iPhone"
        >
    </div>

    <button
        id="register-passkey-btn"
        type="button"
        data-options-url="{{ $registerOptionsUrl }}"
        data-verify-url="{{ $registerVerifyUrl }}"
        @class([
            'mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition disabled:opacity-60',
            'bg-gradient-to-r from-teal-500 to-emerald-400 text-slate-950 hover:opacity-90' => $isTeal,
            'bg-gradient-to-r from-cyan-500 to-sky-400 text-slate-950 hover:opacity-90' => $isDark && ! $isTeal,
            'bg-cyan-600 text-white hover:bg-cyan-500' => ! $isDark,
        ])
    >
        <span id="register-passkey-label">Register passkey</span>
        <svg id="register-passkey-spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
    </button>

    <p id="register-passkey-status" @class(['mt-2 text-xs', 'text-slate-400' => $isDark, 'text-cyan-900' => ! $isDark]) aria-live="polite"></p>
</div>

@once
    @vite(['resources/js/passkey-register.js'])
@endonce
