<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionInactivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $timeoutMinutes = (int) SystemSetting::getValue('session_timeout_minutes', 30);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $timeoutMinutes) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Session expired due to inactivity.');
        }

        $request->session()->put('last_activity_at', now());

        return $next($request);
    }
}
