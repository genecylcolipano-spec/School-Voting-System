<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Passkey Recovery — {{ \App\Support\SchoolBranding::systemName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/passkey-recovery.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-xl font-bold text-slate-900">Lost your passkey?</h1>
        <p class="mt-2 text-sm text-slate-600">
            Submit your Account ID and email. If your details match, a reset enrollment link is sent automatically.
            If delivery fails, an administrator can still issue a manual enrollment link.
            You can also email <a href="mailto:{{ $supportEmail }}" class="text-cyan-700 underline">{{ $supportEmail }}</a>.
        </p>

        <div id="recovery-status" class="mt-4 hidden rounded-lg border px-3 py-2 text-sm" role="status"></div>

        <form id="recovery-form" class="mt-6 space-y-4" data-url="{{ route('login.recovery.request') }}">
            <div>
                <label for="account_id" class="block text-sm font-medium text-slate-700">Account ID</label>
                <input id="account_id" name="account_id" required placeholder="e.g. 600045 or ADMIN-001" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <button type="submit" class="w-full rounded-lg bg-slate-900 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">
                Request passkey reset
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ $loginUrl }}" class="text-cyan-700 hover:underline">Back to login</a>
        </p>
    </div>
</body>
</html>
