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

        if (app()->runningInConsole()) {
            return $configuredUrl;
        }

        $request ??= request();
        $requestHost = strtolower(rtrim((string) $request->getHost(), '.'));

        if (! self::isTrustedHost($requestHost)) {
            return $configuredUrl;
        }

        $configuredParts = parse_url($configuredUrl);
        $configuredScheme = strtolower((string) ($configuredParts['scheme'] ?? 'https'));
        $scheme = $request->isSecure() ? 'https' : $configuredScheme;
        $port = (int) $request->getPort();
        $defaultPort = $scheme === 'https' ? 443 : 80;
        $portSuffix = $port > 0 && $port !== $defaultPort ? ':'.$port : '';

        return $scheme.'://'.$requestHost.$portSuffix;
    }

    public static function isTrustedHost(string $host): bool
    {
        $host = strtolower(rtrim(trim($host), '.'));
        if ($host === '') {
            return false;
        }

        if (! filter_var($host, FILTER_VALIDATE_IP)
            && ! filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        $configuredParts = parse_url((string) config('app.url'));
        $configuredHost = strtolower(rtrim((string) ($configuredParts['host'] ?? ''), '.'));

        $allowedHosts = array_map(
            static function ($candidate): string {
                $candidate = trim((string) $candidate);
                if (str_contains($candidate, '://')) {
                    $candidate = (string) parse_url($candidate, PHP_URL_HOST);
                } else {
                    $candidate = preg_replace('/:\d+$/', '', $candidate) ?? $candidate;
                }

                return strtolower(rtrim($candidate, '.'));
            },
            config('alternova.allowed_hosts', [])
        );

        return $host === $configuredHost || in_array($host, $allowedHosts, true);
    }
}
