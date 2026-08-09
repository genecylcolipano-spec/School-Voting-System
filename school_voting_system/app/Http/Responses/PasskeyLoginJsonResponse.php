<?php

namespace App\Http\Responses;

use App\Services\Auth\RoleRedirectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasskeyLoginJsonResponse implements PasskeyLoginResponseContract
{
    public function __construct(protected RoleRedirectService $redirects) {}

    public function toResponse($request): Response
    {
        $user = $request->user();
        $redirect = $user ? $this->redirects->dashboardPathFor($user) : config('passkeys.redirect', '/dashboard');

        if ($request->wantsJson() || $request->expectsJson()) {
            return new JsonResponse([
                'message' => $user ? "Welcome back, {$user->name}." : 'Signed in successfully.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()->intended($redirect);
    }
}
