<?php

namespace App\Support;

use Illuminate\Http\Request;

class CanonicalUrl
{
    /**
     * Resolve the current public origin without trusting an arbitrary Host
     * header. A request host is used only when it is APP_URL's host or appears
     * in APP_ALLOWED_HOSTS; CLI work falls back to APP_URL.
     */
    public static function base(?Request $request = null): string
    {
        $configuredUrl = rtrim((string) config('app.url'), '/');
        $configuredParts = parse_url($configuredUrl);
        $configuredHost = strtolower(rtrim((string) ($configuredParts['host'] ?? ''), '.'));

        if (app()->runningInConsole()) {
            return $configuredUrl;
        }

        $request ??= request();
        $requestHost = strtolower(rtrim((string) $request->getHost(), '.'));

        if ($requestHost === '' || ! filter_var($requestHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return $configuredUrl;
        }

        $allowedHosts = array_map(
            static function ($host): string {
                $host = trim((string) $host);
                if (str_contains($host, '://')) {
                    $host = (string) parse_url($host, PHP_URL_HOST);
                } else {
                    $host = preg_replace('/:\d+$/', '', $host) ?? $host;
                }

                return strtolower(rtrim($host, '.'));
            },
            config('alternova.allowed_hosts', [])
        );

        $trustedHost = $requestHost === $configuredHost || in_array($requestHost, $allowedHosts, true);

        if (! $trustedHost) {
            return $configuredUrl;
        }

        $configuredScheme = strtolower((string) ($configuredParts['scheme'] ?? 'https'));
        $scheme = $request->isSecure() ? 'https' : $configuredScheme;
        $port = (int) $request->getPort();
        $defaultPort = $scheme === 'https' ? 443 : 80;
        $portSuffix = $port > 0 && $port !== $defaultPort ? ':'.$port : '';

        return $scheme.'://'.$requestHost.$portSuffix;
    }
}
