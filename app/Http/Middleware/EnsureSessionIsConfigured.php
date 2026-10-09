<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs before StartSession so driver/cookie flags are correct.
 * Misconfigured sessions are the usual cause of Filament/Livewire 419 Page Expired.
 */
class EnsureSessionIsConfigured
{
    public function handle(Request $request, Closure $next): Response
    {
        // Empty string domain is invalid — use host-only cookies
        $domain = config('session.domain');
        if ($domain === '' || $domain === false) {
            config(['session.domain' => null]);
        }

        // database driver without sessions table → new session every request → 419 on POST
        if (config('session.driver') === 'database') {
            try {
                $table = (string) config('session.table', 'sessions');
                if ($table === '' || ! Schema::hasTable($table)) {
                    config(['session.driver' => 'file']);
                }
            } catch (\Throwable) {
                config(['session.driver' => 'file']);
            }
        }

        if (config('session.driver') === 'file') {
            $path = config('session.files');
            if (is_string($path) && $path !== '' && ! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }

        // Secure cookies on plain HTTP never stick → 419
        $secure = config('session.secure');
        if ($secure === true || $secure === 1 || $secure === '1' || $secure === 'true') {
            if (! $this->requestLooksHttps($request)) {
                config(['session.secure' => false]);
            }
        }

        $sameSite = strtolower((string) config('session.same_site', 'lax'));
        if (! in_array($sameSite, ['lax', 'strict', 'none'], true)) {
            config(['session.same_site' => 'lax']);
        }

        // SameSite=None requires Secure
        if ($sameSite === 'none' && ! config('session.secure')) {
            if ($this->requestLooksHttps($request)) {
                config(['session.secure' => true]);
            } else {
                config(['session.same_site' => 'lax']);
            }
        }

        return $next($request);
    }

    protected function requestLooksHttps(Request $request): bool
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

        if (str_starts_with(strtolower((string) config('app.url')), 'https://')) {
            return true;
        }

        return false;
    }
}
