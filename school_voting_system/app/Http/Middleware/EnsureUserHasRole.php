<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Unauthorized.');
        }

        $allowed = array_map(function (string $role): UserRole {
            $normalized = str_replace('_', ' ', $role);

            return UserRole::from($normalized);
        }, $roles);

        if (! in_array($user->role, $allowed, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
