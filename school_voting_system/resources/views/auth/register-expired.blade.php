<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enrollment Link Expired — {{ \App\Support\SchoolBranding::systemName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 font-[Instrument_Sans] text-slate-100 antialiased">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-md rounded-2xl border border-white/10 bg-white/5 p-8 shadow-2xl backdrop-blur-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-amber-300/80">{{ \App\Support\SchoolBranding::systemName() }}</p>
            <h1 class="mt-2 text-2xl font-bold text-white">Enrollment Link Expired</h1>
            <p class="mt-2 text-sm text-slate-400">
                Your secure passkey setup link has expired or is no longer valid.
            </p>

            <div class="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-4 text-sm text-amber-100">
                Please start the account setup process again. You will need to confirm and validate your roster details to receive a new setup link.
            </div>

            <a href="{{ $registerUrl }}"
               class="mt-6 flex w-full items-center justify-center rounded-xl bg-cyan-500 py-3 text-sm font-semibold text-slate-950 hover:bg-cyan-400">
                Create Account Again
            </a>

            <p class="mt-6 text-center text-sm text-slate-500">
                Already have a passkey?
                <a href="{{ $loginUrl }}" class="text-cyan-300 hover:text-cyan-200">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
