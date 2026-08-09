<?php

$appUrl = (string) config('app.url');

$normalizeOrigin = static function (string $origin) use ($appUrl): string {
    $origin = trim($origin);

    if ($origin === '') {
        return '';
    }

    if (str_starts_with($origin, 'http://') || str_starts_with($origin, 'https://')) {
        return $origin;
    }

    $parsed = parse_url($appUrl);
    $scheme = $parsed['scheme'] ?? 'http';
    $port = isset($parsed['port']) ? ':'.$parsed['port'] : '';

    return $scheme.'://'.$origin.$port;
};

$originsEnv = env('PASSKEYS_ALLOWED_ORIGINS') ?? env('WEBAUTHN_ORIGINS');
$allowedOrigins = $originsEnv
    ? array_values(array_filter(array_unique(array_map(
        $normalizeOrigin,
        explode(',', (string) $originsEnv),
    ))))
    : [$appUrl];

return [

    /*
    |--------------------------------------------------------------------------
    | Relying Party ID
    |--------------------------------------------------------------------------
    |
    | The relying party ID represents your application in the WebAuthn protocol.
    | This is typically your domain (e.g., "example.com"). Passkeys are bound
    | to this ID and can only be verified on matching domains.
    |
    */

    'relying_party_id' => env('PASSKEYS_RELYING_PARTY_ID', env('WEBAUTHN_ID', parse_url($appUrl, PHP_URL_HOST))),

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | The origins permitted to complete WebAuthn ceremonies. Passkeys bound
    | to the relying party ID above will only verify when the browser
    | reports one of these origins. Defaults to your application URL.
    |
    */

    'allowed_origins' => $allowedOrigins,

    /*
    |--------------------------------------------------------------------------
    | User Handle Secret
    |--------------------------------------------------------------------------
    |
    | Secret used to derive a stable WebAuthn user handle from each user model.
    | Set this explicitly if you rotate your application key.
    |
    */

    'allowed_auth_types' => ['platform'],
    'allowed_user_verification' => ['preferred'],

    'user_handle_secret' => env('PASSKEYS_USER_HANDLE_SECRET', config('app.key')),

    /*
    |--------------------------------------------------------------------------
    | WebAuthn Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in milliseconds for WebAuthn operations. This determines
    | how long users have to complete passkey registration or verification.
    |
    */

    'timeout' => 60000,

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard to use when logging in users with passkeys.
    | This should match your application's primary authentication guard.
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Passkeys Routes Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify which middleware Passkeys will assign to the routes
    | that it registers with the application. If necessary, you may change
    | these middleware but typically this provided default is preferred.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Passkeys Management Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may specify the middleware applied to passkey management routes
    | that create or delete passkeys. By default, Laravel's password
    | confirmation middleware is used.
    |
    */

    'management_middleware' => [],

    /*
    |--------------------------------------------------------------------------
    | Passkeys Throttling
    |--------------------------------------------------------------------------
    |
    | Middleware used to throttle passkey endpoints. Set to null to disable.
    |
    */

    'throttle' => 'throttle:6,1',

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    |
    | The path to redirect to after successful passkey verification.
    |
    */

    'redirect' => '/dashboard',

];
