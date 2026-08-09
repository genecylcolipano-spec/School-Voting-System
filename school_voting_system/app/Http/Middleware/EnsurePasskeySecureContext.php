<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebAuthn requires a secure context (HTTPS or localhost).
 */
class EnsurePasskeySecureContext
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            app()->environment('production')
            && ! $request->isSecure()
        ) {
            abort(403, 'Passkey authentication requires HTTPS in production.');
        }

        return $next($request);
    }
}
