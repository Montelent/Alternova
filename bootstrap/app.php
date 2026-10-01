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
 | Hostinger / shared hosting: document root is public_html (app root),
 | not public_html/public. Point Laravel's public path at the base path
 | so public_path('uploads') resolves to public_html/uploads.
 */
if (! is_file($basePath.'/public/index.php')) {
    $app->usePublicPath($basePath);
}

return $app;
