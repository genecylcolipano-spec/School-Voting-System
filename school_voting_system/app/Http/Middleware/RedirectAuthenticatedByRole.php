<?php

namespace App\Http\Middleware;

use App\Services\Auth\RoleRedirectService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedByRole
{
    public function __construct(protected RoleRedirectService $redirects) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return redirect($this->redirects->dashboardPathFor($request->user()));
        }

        return $next($request);
    }
}
