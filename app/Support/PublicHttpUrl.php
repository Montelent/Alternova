<?php

namespace App\Support;

/**
 * Guard server-side HTTP requests against SSRF to local/private infrastructure.
 *
 * Callers may use safeRedirectOptions() to follow a small number of redirects
 * only after each resolved destination passes this same public-address check.
 */
class PublicHttpUrl
{
    public static function isSafe(string $url): bool
    {
        try {
            $parts = parse_url(trim($url));

            if (! is_array($parts)) {
                return false;
            }

            $scheme = strtolower((string) ($parts['scheme'] ?? ''));
            $host = strtolower(trim(rtrim((string) ($parts['host'] ?? ''), '.'), '[]'));

            if (! in_array($scheme, ['http', 'https'], true)
                || $host === ''
                || isset($parts['user'])
                || isset($parts['pass'])
                || str_contains($host, '%')
                || in_array($host, ['localhost', 'localhost.localdomain'], true)
                || preg_match('/\.(?:local|internal|localhost|test|invalid|example|onion)$/i', $host)
            ) {
                return false;
            }

            $addresses = [];

            if (filter_var($host, FILTER_VALIDATE_IP)) {
                $addresses[] = $host;
            } else {
                $records = @dns_get_record($host, DNS_A | DNS_AAAA);

                foreach ($records ?: [] as $record) {
                    if (! empty($record['ip'])) {
                        $addresses[] = $record['ip'];
                    }
                    if (! empty($record['ipv6'])) {
                        $addresses[] = $record['ipv6'];
                    }
                }
            }

            if ($addresses === []) {
                return false;
            }

            foreach (array_unique($addresses) as $address) {
                if (! self::isPublicAddress($address)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private static function isPublicAddress(string $address): bool
    {
        // PHP 8.2 may classify IPv4-mapped IPv6 addresses as public even when
        // their embedded IPv4 target is loopback/private. Normalize and check
        // the embedded address explicitly before applying the normal filters.
        $packed = @inet_pton($address);
        $mappedPrefix = str_repeat("\\0", 10)."\\xff\\xff";

        if (is_string($packed) && strlen($packed) === 16 && substr($packed, 0, 12) === $mappedPrefix) {
            $ipv4 = inet_ntop(substr($packed, 12));

            return is_string($ipv4) && filter_var(
                $ipv4,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * Guzzle redirect policy: follow only a small number of redirects, and
     * validate every resolved destination before the client follows it.
     *
     * @return array{max: int, on_redirect: \Closure}
     */
    public static function safeRedirectOptions(): array
    {
        return [
            'max' => 3,
            'on_redirect' => static function ($request, $response, $uri): void {
                if (! self::isSafe((string) $uri)) {
                    throw new \RuntimeException('Blocked redirect to a non-public address.');
                }
            },
        ];
    }

}
