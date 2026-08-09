<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enrollment API routes run after the signed enrollment page sets passkey.bootstrap_user_id.
 */
class EnsurePasskeyBootstrapSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('passkey.bootstrap_user_id')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Enrollment session expired. Open a new enrollment link from your administrator.',
                ], 403);
            }

            return redirect()->route('login')->with(
                'status',
                'Enrollment session expired. Request a new passkey enrollment link.'
            );
        }

        return $next($request);
    }
}
