<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\EnsureNotInstalled;
use App\Http\Middleware\EnsureSessionIsConfigured;
use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\ForcePreferredUrl;
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

        // Only trust forwarded headers from explicitly configured reverse proxies.
        // Set TRUSTED_PROXIES to a comma-separated list of proxy IPs/CIDRs per host.
        $proxies = config('alternova.trusted_proxies', []);
        if (is_array($proxies) && $proxies !== []) {
            $middleware->trustProxies(at: $proxies);
        }

        // Keep Laravel's normal CSRF protection enabled for every state-changing
        // endpoint, including Livewire. Livewire component checksums are not a
        // substitute for CSRF validation.
        $middleware->web(prepend: [
            EnsureSessionIsConfigured::class,
        ]);

        $middleware->web(append: [
            ForcePreferredUrl::class, // host (www/non-www) + HTTPS from APP_URL
            RedirectIfNotInstalled::class,
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
            PreventDemoWrites::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Use framework default 419 handling so Livewire can offer "refresh page"
    })->create();
