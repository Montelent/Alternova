<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\ForceHttps;
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

        // Trust proxy headers on shared hosting (Hostinger, Cloudflare, etc.)
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            ForceHttps::class,
            RedirectIfNotInstalled::class,
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
        ]);

        // Also cover any non-web routes if added later
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Never leak stack traces to clients in production (Laravel already hides when APP_DEBUG=false)
    })->create();
