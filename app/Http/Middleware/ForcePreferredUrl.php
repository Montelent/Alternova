<?php

namespace App\Http\Middleware;

use App\Support\CanonicalUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Canonical scheme from APP_URL; permit additional explicitly allow-listed domains.
 *
 * - APP_URL is the fallback host; explicitly allow-listed hosts remain active
 * - http → https when APP_URL uses https
 * - Safe methods only (never 301 POST — Livewire / CSRF)
 * - Skips installer, health check, and local/dev preferred hosts
 */
class ForcePreferredUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $forcePreferredUrl = $this->envBool('FORCE_PREFERRED_URL', true);

        if ($request->is('install', 'install/*', 'up')) {
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
        if (str_contains($currentHost, ':')) {
            [$host, $port] = explode(':', $currentHost, 2);
            if (in_array($port, ['80', '443'], true)) {
                $currentHost = $host;
            }
        }
        if (str_contains($preferredHost, ':')) {
            [$host, $port] = explode(':', $preferredHost, 2);
            if (in_array($port, ['80', '443'], true)) {
                $preferredHost = $host;
            }
        }

        $currentHostAllowed = CanonicalUrl::isTrustedHost($currentHost);
        $currentIsHttps = $this->requestIsHttps($request);
        $schemeMismatch = $preferredScheme === 'https' && ! $currentIsHttps;
        $safeMethod = $request->isMethodSafe();

        if (! $currentHostAllowed) {
            // Redirect only safe requests. Never process state-changing requests
            // under an untrusted Host header, even when redirects are disabled.
            if ($forcePreferredUrl && $safeMethod) {
                $uri = $request->getRequestUri();
                if ($uri === '' || $uri[0] !== '/') {
                    $uri = '/'.$uri;
                }

                return redirect()->to($preferredScheme.'://'.$preferredHost.$uri, 301);
            }

            abort(400, 'Unrecognized request host.');
        }

        if ($forcePreferredUrl && $schemeMismatch) {
            if (! $safeMethod) {
                abort(400, 'HTTPS is required for state-changing requests.');
            }

            $uri = $request->getRequestUri();
            if ($uri === '' || $uri[0] !== '/') {
                $uri = '/'.$uri;
            }

            return redirect()->to($preferredScheme.'://'.$currentHost.$uri, 301);
        }

        return $next($request);
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
        // Laravel only honors forwarded scheme headers from proxies explicitly
        // configured in TRUSTED_PROXIES. Never trust raw client-supplied headers.
        return $request->isSecure();
    }
}
