<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(new \App\Jobs\SendTalentVotingClosingSoonJob(24))->hourly();
        $schedule->command('portal:process-scheduled-elections')->everyMinute();
        $schedule->command('portal:prune-notifications')->dailyAt('03:15');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo('/');
        $middleware->redirectUsersTo('/dashboard');
        $middleware->web(append: [
            \App\Http\Middleware\RedirectPasskeyHostToLocalhost::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'guest.portal' => \App\Http\Middleware\RedirectAuthenticatedByRole::class,
            'passkey.secure' => \App\Http\Middleware\EnsurePasskeySecureContext::class,
            'passkey.bootstrap' => \App\Http\Middleware\EnsurePasskeyBootstrapSession::class,
            'session.inactivity' => \App\Http\Middleware\EnforceSessionInactivity::class,
            'admin.ip' => \App\Http\Middleware\CheckAdminIpWhitelist::class,
            'permission' => \App\Http\Middleware\EnsureStaffPermission::class,
            'app.maintenance' => \App\Http\Middleware\EnsureNotInAppMaintenance::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
