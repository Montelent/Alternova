<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect HTTP → HTTPS in production when APP_URL is https.
 * Safe on shared hosting behind proxies when TrustProxies / HTTPS detection works.
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

        if ($request->isSecure()) {
            return $next($request);
        }

        // Avoid redirect loops on local / CLI
        if ($request->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || $request->server('HTTP_X_FORWARDED_SSL') === 'on') {
            return $next($request);
        }

        $url = 'https://'.$request->getHttpHost().$request->getRequestUri();

        return redirect()->to($url, 301);
    }
}
