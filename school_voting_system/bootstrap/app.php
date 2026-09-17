<?php

use App\Support\SessionExpired;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        $schedule->job(new \App\Jobs\SendTalentVotingClosingSoonJob(24))->hourly();
        $schedule->command('portal:process-scheduled-elections')->everyMinute();
        $schedule->command('portal:process-scheduled-announcements')->everyMinute();
        $schedule->command('portal:prune-notifications')->dailyAt('03:15');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo('/');
        $middleware->redirectUsersTo('/dashboard');
        $middleware->web(append: [
            \App\Http\Middleware\RedirectPasskeyHostToLocalhost::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/paymongo',
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
            'platform.module' => \App\Http\Middleware\EnsurePlatformModule::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            if ($response->getStatusCode() !== 419) {
                return $response;
            }

            $message = SessionExpired::MESSAGE;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 419);
            }

            if ($request->hasSession()) {
                return redirect()
                    ->back()
                    ->withInput($request->except(['password', 'password_confirmation', '_token']))
                    ->with('error', $message);
            }

            return $response;
        });
    })->create();
