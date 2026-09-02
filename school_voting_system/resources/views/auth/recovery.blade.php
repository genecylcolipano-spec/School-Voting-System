<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-dvh overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>Passkey Recovery — {{ \App\Support\SchoolBranding::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/passkey-recovery.js'])
    <script>
        if (window.location.hostname === '127.0.0.1' || window.location.hostname === '[::1]') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="h-dvh overflow-hidden bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="relative flex h-dvh items-center justify-center px-4 py-3 sm:py-4">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(56,189,248,0.16),_transparent_55%)]"></div>

        <div class="relative w-[calc(100%-32px)] max-w-[440px] min-w-0 rounded-2xl border border-white/10 bg-white/5 p-4 shadow-2xl backdrop-blur-xl sm:p-5">
            <h1 class="text-center text-xl font-bold text-white sm:text-2xl">Lost your passkey?</h1>
            <p class="mt-1 text-center text-sm text-slate-400">
                Request a secure recovery link to register a new passkey on this or another device.
            </p>

            @if (session('status'))
                <div class="mt-3 break-words rounded-xl border border-cyan-400/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-100" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mt-3 break-words rounded-xl border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-sm text-amber-100" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div id="recovery-status" class="mt-3 hidden break-words rounded-xl border px-3 py-2 text-sm" role="status" aria-live="polite"></div>

            <form
                id="recovery-form"
                class="mt-3"
                method="POST"
                action="{{ route('login.recovery.request') }}"
                data-url="{{ route('login.recovery.request') }}"
            >
                @csrf

                <div>
                    <label for="account_id" class="block text-sm font-medium text-slate-200">Account ID</label>
                    <input
                        id="account_id"
                        name="account_id"
                        type="text"
                        required
                        autocomplete="username"
                        inputmode="text"
                        autocapitalize="none"
                        spellcheck="false"
                        value="{{ old('account_id') }}"
                        placeholder="e.g. 600045 "
                        class="mt-1 w-full min-w-0 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-300/40"
                    >
                    @error('account_id')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-2.5">
                    <label for="email" class="block text-sm font-medium text-slate-200">Email Address</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="email"
                        inputmode="email"
                        autocapitalize="none"
                        spellcheck="false"
                        value="{{ old('email') }}"
                        placeholder="e.g. name@gmail.com"
                        class="mt-1 w-full min-w-0 rounded-xl border border-white/10 bg-slate-950/60 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:border-cyan-400/60 focus:outline-none focus:ring-2 focus:ring-cyan-300/40"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <p class="mt-2.5 text-center text-xs text-slate-500">
                    The recovery link expires after {{ $resetExpirationMinutes }} minutes and can only be used once.
                </p>

                <button
                    type="submit"
                    class="mt-3 flex w-full items-center justify-center gap-3 rounded-xl bg-cyan-500 px-4 py-2.5 text-sm font-semibold text-slate-950 transition hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-300/60 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span id="recovery-submit-label">Request Passkey Recovery</span>
                    <svg id="recovery-spinner" class="hidden h-5 w-5 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-3 border-t border-white/10 pt-3 text-center">
                <p class="text-sm font-medium text-slate-300">Need help?</p>
                <p class="mt-1 text-xs text-slate-500">
                    Contact your school administrator at
                    <a href="mailto:{{ $supportEmail }}" class="break-all text-cyan-300/80 underline hover:text-cyan-200">{{ $supportEmail }}</a>
                </p>
                <p class="mt-2">
                    <a href="{{ $loginUrl }}" class="text-xs text-slate-400 transition hover:text-slate-300">Back to Login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
