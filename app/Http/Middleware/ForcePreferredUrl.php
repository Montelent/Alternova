<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Canonical host + scheme from APP_URL (chosen during install / .env).
 *
 * - www vs non-www: the host inside APP_URL is the only public host (301)
 * - http → https when APP_URL uses https
 * - Safe methods only (never 301 POST — Livewire / CSRF)
 * - Skips installer, health check, and local/dev preferred hosts
 *
 * Runs on every environment except when the preferred host is local,
 * so shared hosting with APP_ENV=local still gets correct redirects.
 */
class ForcePreferredUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        // Explicit opt-out
        if ($this->envBool('FORCE_PREFERRED_URL', true) === false) {
            return $next($request);
        }

        // Installer must work on any host until APP_URL is written
        if ($request->is('install', 'install/*', 'up')) {
            return $next($request);
        }

        // Never redirect mutating requests (Livewire, forms)
        if (! $request->isMethodSafe()) {
            return $next($request);
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl === '' || ! preg_match('#^https?://#i', $appUrl)) {
            return $next($request);
        }

        $parts = parse_url($appUrl);
        $preferredHost = strtolower((string) ($parts['host'] ?? ''));
        $preferredScheme = strtolower((string) ($parts['scheme'] ?? 'https'));

        if ($preferredHost === '' || $this->isLocalHost($preferredHost)) {
            return $next($request);
        }

        $currentHost = strtolower($request->getHost());
        $currentIsHttps = $this->requestIsHttps($request);
        $currentScheme = $currentIsHttps ? 'https' : 'http';

        // Treat "www.example.com" vs "example.com" as mismatch
        $hostMismatch = ! hash_equals($preferredHost, $currentHost);
        $schemeMismatch = $preferredScheme === 'https' && $currentScheme !== 'https';

        if (! $hostMismatch && ! $schemeMismatch) {
            return $next($request);
        }

        $uri = $request->getRequestUri();
        if ($uri === '' || $uri[0] !== '/') {
            $uri = '/'.$uri;
        }

        $target = $preferredScheme.'://'.$preferredHost.$uri;

        return redirect()->to($target, 301);
    }

    protected function envBool(string $key, bool $default): bool
    {
        $val = env($key);
        if ($val === null) {
            return $default;
        }

        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    protected function isLocalHost(string $host): bool
    {
        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($host, '.local')
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
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
            || strtolower((string) $request->server('HTTP_X_FORWARDED_PROTO')) === 'https') {
            return true;
        }

        return false;
    }
}
