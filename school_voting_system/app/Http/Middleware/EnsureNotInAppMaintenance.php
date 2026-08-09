<?php

namespace App\Http\Middleware;

use App\Services\SuperAdmin\MaintenanceModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application-level maintenance gate (System Settings), separate from Laravel `artisan down`.
 * Super Administrators may bypass when configured.
 */
class EnsureNotInAppMaintenance
{
    public function __construct(protected MaintenanceModeService $maintenance) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->maintenance->isEnabled()) {
            return $next($request);
        }

        $user = $request->user();

        // Always allow Super Admin System Management + logout to avoid lockout.
        if ($user?->isSuperAdmin() && $request->routeIs(
            'super-admin.system.*',
            'logout',
        )) {
            return $next($request);
        }

        if ($this->maintenance->userMayBypass($user)) {
            return $next($request);
        }

        // Allow logout while the maintenance page is shown.
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->maintenance->message(),
            ], 503);
        }

        return response()->view('maintenance', [
            'message' => $this->maintenance->message(),
            'returnAt' => $this->maintenance->estimatedReturnAt(),
        ], 503);
    }
}
