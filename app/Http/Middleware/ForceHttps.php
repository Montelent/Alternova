<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect HTTP → HTTPS in production when APP_URL is https.
 * Never 301 POST/PUT/PATCH (that causes CSRF 419 / lost Livewire payloads).
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isProduction()) {
            return $next($request);
        }

        $appUrl = (string) config('app.url');
        if (! str_starts_with(strtolower($appUrl), 'https://')) {
            return $next($request);
        }

        if ($this->requestIsHttps($request)) {
            return $next($request);
        }

        // Livewire / Filament / form posts must not be redirected away
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $url = 'https://'.$request->getHttpHost().$request->getRequestUri();

        return redirect()->to($url, 301);
    }

    protected function requestIsHttps(Request $request): bool
    {
        if ($request->isSecure()) {
            return true;
        }

        $proto = strtolower((string) $request->headers->get('X-Forwarded-Proto', ''));
        if ($proto === 'https' || str_starts_with($proto, 'https,')) {
            return true;
        }

        if ($request->headers->get('X-Forwarded-Ssl') === 'on') {
            return true;
        }

        if ((string) $request->server('HTTPS') === 'on'
            || (string) $request->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            return true;
        }

        return false;
    }
}
