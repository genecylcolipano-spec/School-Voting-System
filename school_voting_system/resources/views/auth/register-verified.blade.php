<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Identity Verified — {{ \App\Support\SchoolBranding::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-cyan-300/80">{{ \App\Support\SchoolBranding::systemName() }}</p>
            <h1 class="mt-2 text-2xl font-bold text-white">Identity Verified</h1>
            <p class="mt-2 text-sm text-slate-400">
                Your information matches the official school roster.
            </p>

            @if (session('status'))
                <p class="mt-4 rounded-lg border border-cyan-500/30 bg-cyan-500/10 px-3 py-2 text-sm text-cyan-100">{{ session('status') }}</p>
            @endif

            <div class="mt-6 space-y-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-4 text-sm text-emerald-100">
                <p>Your secure passkey setup is ready.</p>
                <p>This setup link expires in <span class="font-semibold">{{ $expirationHours }} hours</span>.</p>
                @if ($emailSent ?? false)
                    <p class="text-emerald-200/80">We also emailed instructions to <span class="font-medium">{{ $email }}</span>.</p>
                @endif
            </div>

            <a href="{{ $enrollUrl }}"
               class="mt-6 flex w-full items-center justify-center rounded-xl bg-cyan-500 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400">
                Continue to Passkey Setup
            </a>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already finished setup?
                <a href="{{ $loginUrl }}" class="text-cyan-300 hover:text-cyan-200">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
