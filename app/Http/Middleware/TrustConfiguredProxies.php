<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustConfiguredProxies
{
    public function handle(Request $request, Closure $next): Response
    {
        $proxies = config('alternova.trusted_proxies', []);
        if (! is_array($proxies)) {
            $proxies = [];
        }

        $proxies = array_values(array_filter(
            $proxies,
            static fn ($proxy): bool => is_string($proxy) && trim($proxy) !== '' && trim($proxy) !== '*'
        ));

        $trustedHeaders = Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_HOST
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_PREFIX;

        // This middleware runs per request, after configuration has loaded,
        // so cached config and .env deployments use the same explicit allowlist.
        Request::setTrustedProxies($proxies, $proxies === [] ? 0 : $trustedHeaders);

        return $next($request);
    }
}
