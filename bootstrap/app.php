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

        /*
         | Livewire POSTs to /livewire/update. On multi-process shared hosting (Hostinger),
         | file session locks + concurrent Livewire requests often produce false CSRF 419s.
         | Livewire still validates component checksums (APP_KEY). Keep CSRF on all other routes.
         */
        $middleware->validateCsrfTokens(except: [
            'livewire/update',
            'livewire/*',
        ]);

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
        // Use framework default 419 handling so Livewire can offer "refresh page"
    })->create();
