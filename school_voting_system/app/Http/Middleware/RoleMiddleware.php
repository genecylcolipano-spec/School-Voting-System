<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            Log::warning('Role middleware blocked: unauthenticated or missing role.', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_id' => $user?->id,
            ]);

            abort(403, 'Unauthorized.');
        }

        $allowed = array_map(function (string $role): UserRole {
            return UserRole::fromRouteParam($role);
        }, $roles);

        if (! in_array($user->role, $allowed, true)) {
            Log::warning('Role middleware blocked: insufficient role.', [
                'path' => $request->path(),
                'user_id' => $user->id,
                'account_id' => $user->account_id,
                'user_role' => $user->role->value,
                'required_roles' => array_map(fn (UserRole $r) => $r->value, $allowed),
                'ip' => $request->ip(),
            ]);

            abort(403, 'You do not have permission to access this area.');
        }

        Log::debug('Role middleware passed.', [
            'path' => $request->path(),
            'user_id' => $user->id,
            'account_id' => $user->account_id,
            'role' => $user->role->value,
            'ip' => $request->ip(),
        ]);

        return $next($request);
    }
}
