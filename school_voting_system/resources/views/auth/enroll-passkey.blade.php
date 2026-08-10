<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register Passkey — {{ \App\Support\SchoolBranding::systemName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/passkey-register.js'])
    <script>
        if (window.location.hostname === '127.0.0.1') {
            const port = window.location.port ? ':' + window.location.port : '';
            window.location.replace(window.location.protocol + '//localhost' + port + window.location.pathname + window.location.search);
        }
    </script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg">
        <h1 class="text-xl font-bold text-slate-900">Register your passkey</h1>

        @if ($user)
            <p class="mt-2 text-sm text-slate-600">
                Setting up passwordless access for <strong>{{ $user->name }}</strong> ({{ $user->account_id }}).
            </p>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-700">
                Role: {{ str($user->role?->value ?? 'unknown')->title() }}
            </p>
            @if (session()->has(\App\Services\Auth\PasskeyRecoveryTokenService::SESSION_RECOVERY_REQUEST_ID))
                <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    Registering a new passkey will revoke your previous passkeys on this account.
                </p>
            @endif
        @elseif (! empty($pending))
            <p class="mt-2 text-sm text-slate-600">
                Setting up passwordless access for
                <strong>{{ trim(($pending['first_name'] ?? '').' '.($pending['last_name'] ?? '')) }}</strong>
                ({{ $pending['account_id'] ?? '' }}).
            </p>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-cyan-700">
                Student registration
            </p>
        @endif

        @if (session('status'))
            <p class="mt-4 rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-sm text-cyan-800">{{ session('status') }}</p>
        @endif

        <div class="mt-6">
            <x-passkey-register
                :register-options-url="$registerOptionsUrl"
                :register-verify-url="$registerVerifyUrl"
            />
        </div>
    </div>
</body>
</html>
