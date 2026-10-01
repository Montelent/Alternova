<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\ThrottleApi;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$basePath = dirname(__DIR__);

$app = Application::configure(basePath: $basePath)
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

        $middleware->web(append: [
            RedirectIfNotInstalled::class,
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

/*
 | ALWAYS use the app root as the public path.
 | Hostinger document root is public_html (where index.php lives).
 | Even if a leftover public/ folder exists, do not use it.
 */
$app->usePublicPath($basePath);

return $app;
