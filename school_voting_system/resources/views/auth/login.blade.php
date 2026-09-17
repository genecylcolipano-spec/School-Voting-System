<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-dvh overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>{{ \App\Support\SchoolBranding::systemName() }} — Passkey Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/passkey-auth.js'])
    <script>
        if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="h-dvh overflow-hidden bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    @php
        $periodLabel = \App\Support\SchoolBranding::periodLabel();
        $poweredBy = \App\Support\SchoolBranding::poweredBy();
        $registrationEnabled = $registrationEnabled ?? true;
        $recoveryEnabled = $recoveryEnabled ?? true;
        $inAppBrowser = \App\Support\InAppBrowser::detect(request()->userAgent());
    @endphp

    <div class="relative flex h-dvh items-center justify-center px-4 py-3 sm:py-6">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_55%)]"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-5 text-center">
                @if ($loginLogo = \App\Support\SchoolBranding::logoUrl(withFallback: false))
                    <img src="{{ $loginLogo }}" alt="{{ \App\Support\SchoolBranding::schoolName() }}" class="mx-auto h-14 w-14 rounded-full border border-white/10 bg-white object-contain p-0.5 shadow-lg shadow-cyan-900/30" onerror="this.classList.add('hidden'); const fallback = this.nextElementSibling; if (fallback) { fallback.classList.remove('hidden'); fallback.classList.add('flex'); }">
                    <div class="mx-auto hidden h-14 w-14 items-center justify-center rounded-full border border-white/10 bg-gradient-to-br from-cyan-500 to-sky-400 text-slate-950 shadow-lg shadow-cyan-900/30" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @else
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-white/10 bg-gradient-to-br from-cyan-500 to-sky-400 text-slate-950 shadow-lg shadow-cyan-900/30" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                @endif
                <p class="mt-3 text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/80">{{ \App\Support\SchoolBranding::systemName() }}</p>
                @if ($poweredBy !== '')
                    <p class="mt-1 text-xs text-slate-500">{{ $poweredBy }}</p>
                @endif
                <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">Secure Passkey Portal</h1>
                @if ($periodLabel !== '')
                    <p class="mt-2 text-sm text-slate-400">{{ $periodLabel }}</p>
                @endif
            </div>

            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 shadow-2xl backdrop-blur-xl sm:p-6">
                @if (session('error'))
                    <div class="mb-3 break-words rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-3 break-words rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-100" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @include('auth.partials.in-app-browser-gate', ['inApp' => $inAppBrowser])

                <div
                    id="passkey-supported-panel"
                    @class(['hidden' => $inAppBrowser['blocked']])
                    @if ($inAppBrowser['blocked']) hidden @endif
                >
                <div id="passkey-status" class="mb-3 hidden rounded-xl border px-3 py-2 text-sm" role="status" aria-live="polite"></div>

                <button
                    id="passkey-login-btn"
                    type="button"
                    class="flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-500 px-4 py-4 text-base font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c.83 0 1.5-.9 1.5-2s-.67-2-1.5-2-1.5.9-1.5 2 .67 2 1.5 2zm0 0v3m0 3.5c-3.5 0-6-2.1-6-5.5 0-1.2.4-2.3 1-3.2m11 3.2c0 3.4-2.5 5.5-6 5.5m5-8.7c.6.9 1 2 1 3.2M8.2 7.3C9.2 6.5 10.5 6 12 6c1.5 0 2.8.5 3.8 1.3"/>
                    </svg>
                    <span id="passkey-login-label">Sign in with Passkey</span>
                    <svg id="passkey-spinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>

                <p class="mt-3 text-center text-sm text-slate-400">
                    Passwordless sign-in using your device fingerprint, face, or PIN.
                </p>

                <p class="mt-4 flex flex-col gap-2 text-center text-xs text-slate-400">
                    @if ($registrationEnabled)
                        <span>
                            Don't have an account?
                            <a href="{{ route('register') }}" class="font-medium text-cyan-300 hover:text-cyan-200">Create one</a>
                        </span>
                    @endif
                    @if ($recoveryEnabled)
                        <span>
                            Forgot your passkey?
                            <a href="{{ route('login.recovery') }}" class="font-medium text-cyan-300 hover:text-cyan-200">Recover access</a>
                        </span>
                    @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.passkeyPortal = {
            loginOptionsUrl: @json($loginOptionsUrl),
            loginVerifyUrl: @json($loginVerifyUrl),
        };
    </script>
</body>
</html>
