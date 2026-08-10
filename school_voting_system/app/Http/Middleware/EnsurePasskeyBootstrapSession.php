<?php

namespace App\Http\Middleware;

use App\Services\Auth\StudentRegistrationPasskeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enrollment API routes require either:
 * - staff/bootstrap enrollment: passkey.bootstrap_user_id, or
 * - public roster registration: pending registration session after token enroll.
 */
class EnsurePasskeyBootstrapSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasBootstrapUser = $request->session()->has('passkey.bootstrap_user_id');
        $hasPendingRegistration = $request->session()->has(StudentRegistrationPasskeyService::SESSION_PENDING);

        if (! $hasBootstrapUser && ! $hasPendingRegistration) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Enrollment session expired. Open a new enrollment link or start Create Account again.',
                ], 403);
            }

            return redirect()->route('register')->with(
                'status',
                'Enrollment session expired. Please create your account again.'
            );
        }

        return $next($request);
    }
}
