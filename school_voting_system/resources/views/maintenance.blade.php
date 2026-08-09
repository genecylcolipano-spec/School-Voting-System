<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance — {{ \App\Support\SchoolBranding::systemName() }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="mx-auto flex min-h-screen max-w-lg flex-col items-center justify-center px-6 py-16 text-center">
        <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-300 ring-1 ring-amber-500/30">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wide text-amber-300">{{ \App\Support\SchoolBranding::systemName() }}</p>
        @if ($poweredBy = \App\Support\SchoolBranding::poweredBy())
            <p class="mt-1 text-xs text-slate-500">{{ $poweredBy }}</p>
        @endif
        <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-amber-300/70">Maintenance Mode</p>
        <h1 class="mt-3 text-2xl font-bold text-white sm:text-3xl">We'll be right back</h1>
        <p class="mt-4 text-sm leading-relaxed text-slate-400">{{ $message }}</p>
        @if ($returnAt)
            <p class="mt-4 rounded-xl border border-slate-800 bg-slate-900/70 px-4 py-3 text-sm text-slate-300">
                Estimated return:
                <span class="font-semibold text-white">{{ $returnAt->timezone(config('app.timezone'))->format('M d, Y g:i A') }}</span>
            </p>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="mt-8">
            @csrf
            <button type="submit" class="text-sm font-semibold text-violet-300 hover:text-violet-200">
                Back to login
            </button>
        </form>
    </div>
</body>
</html>
