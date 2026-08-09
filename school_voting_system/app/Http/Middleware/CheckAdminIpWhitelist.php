<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || (! $user->isAdmin() && ! $user->isSuperAdmin())) {
            return $next($request);
        }

        if (! SystemSetting::getValue('ip_whitelist_enabled', false)) {
            return $next($request);
        }

        $whitelist = SystemSetting::getValue('ip_whitelist', []);

        if (! is_array($whitelist) || $whitelist === []) {
            return $next($request);
        }

        $ip = (string) $request->ip();

        if (! in_array($ip, $whitelist, true)) {
            abort(403, 'Your IP address is not authorized for admin access.');
        }

        return $next($request);
    }
}
