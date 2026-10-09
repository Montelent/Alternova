<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\EnsureSessionIsConfigured;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\PreventDemoWrites;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ThrottleApi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'not.installed' => EnsureNotInstalled::class,
            'installed' => RedirectIfNotInstalled::class,
            'api.key' => AuthenticateApiKey::class,
            'api.throttle' => ThrottleApi::class,
        ]);

        $middleware->trustProxies(at: '*');

        // Before StartSession: fix driver / Secure / domain so CSRF tokens stick
        $middleware->web(prepend: [
            EnsureSessionIsConfigured::class,
        ]);

        $middleware->web(append: [
            ForceHttps::class,
            RedirectIfNotInstalled::class,
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
            PreventDemoWrites::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()
                || $request->is('livewire/*')
                || $request->header('X-Livewire')
            ) {
                return response()->json([
                    'message' => 'Your session expired. Refresh the page and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->with('error', 'Your session expired. Please try again.');
        });
    })->create();
