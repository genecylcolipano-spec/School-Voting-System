<?php

namespace App\Http\Middleware;

use App\Support\PlatformModules;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformModule
{
    public function handle(Request $request, Closure $next): Response
    {
        $module = PlatformModules::moduleForRoute($request->route()?->getName());

        if ($module === null || PlatformModules::enabled($module)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->isSuperAdmin()) {
            return $next($request);
        }

        if ($user?->isAdmin()) {
            abort(403, 'This module is currently disabled in System Settings.');
        }

        abort(404);
    }
}
