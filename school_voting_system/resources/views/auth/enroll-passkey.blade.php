<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-dvh overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Register Passkey — {{ \App\Support\SchoolBranding::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/passkey-register.js'])
    <script>
        if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="h-dvh overflow-hidden bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    @php
        $enrollName = $user?->name
            ?? trim(($pending['first_name'] ?? '').' '.($pending['last_name'] ?? ''));
        $enrollAccountId = $user?->account_id ?? ($pending['account_id'] ?? '');
        $enrollRole = $user?->roleLabel() ?? 'Student';
        $isRecovery = session()->has(\App\Services\Auth\PasskeyRecoveryTokenService::SESSION_RECOVERY_REQUEST_ID);
    @endphp

    <div class="relative flex h-dvh items-center justify-center px-4 py-3 sm:py-4">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_55%)]"></div>

        <div class="relative w-full max-w-md">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-xl sm:p-5">
                <div class="mx-auto mb-2 flex h-9 w-9 items-center justify-center rounded-2xl border border-cyan-400/20 bg-cyan-500/10 text-cyan-200" aria-hidden="true">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c.83 0 1.5-.9 1.5-2s-.67-2-1.5-2-1.5.9-1.5 2 .67 2 1.5 2zm0 0v3m0 3.5c-3.5 0-6-2.1-6-5.5 0-1.2.4-2.3 1-3.2m11 3.2c0 3.4-2.5 5.5-6 5.5m5-8.7c.6.9 1 2 1 3.2M8.2 7.3C9.2 6.5 10.5 6 12 6c1.5 0 2.8.5 3.8 1.3"/>
                    </svg>
                </div>

                <h1 class="text-center text-xl font-bold text-white sm:text-2xl">Register your passkey</h1>
                <p class="mt-1 text-center text-sm text-slate-400">
                    Name this device, then confirm with fingerprint, face, or PIN.
                </p>

                @if ($enrollName !== '' || $enrollAccountId !== '')
                    <div class="mt-3 rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2">
                        <p class="text-sm font-semibold text-white">{{ $enrollName }}</p>
                        @if ($enrollAccountId !== '')
                            <p class="mt-0.5 font-mono text-xs text-slate-400">{{ $enrollAccountId }}</p>
                        @endif
                        <p class="mt-1.5">
                            <span class="inline-flex rounded-full border border-cyan-400/30 bg-cyan-500/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-cyan-200">{{ $enrollRole }}</span>
                        </p>
                    </div>
                @endif

                @if ($isRecovery)
                    <p class="mt-3 rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100">
                        Registering a new passkey will revoke your previous passkeys on this account.
                    </p>
                @endif

                @if (session('status'))
                    <p class="mt-3 rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-100">{{ session('status') }}</p>
                @endif

                <div class="mt-3">
                    <x-passkey-register
                        theme="dark"
                        compact
                        :register-options-url="$registerOptionsUrl"
                        :register-verify-url="$registerVerifyUrl"
                    />
                </div>

                <p class="mt-2 text-center text-xs text-slate-500">
                    No password is required. Your private key never leaves this device.
                </p>

                <p class="mt-2 text-center text-xs">
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-slate-300">Already have a passkey? Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
