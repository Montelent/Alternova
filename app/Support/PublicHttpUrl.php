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
            $host = strtolower(trim(rtrim((string) ($parts['host'] ?? ''), '[]')));

            if (! in_array($scheme, ['http', 'https'], true)
                || $host === ''
                || isset($parts['user'])
                || isset($parts['pass'])
                || str_contains($host, '%')
                || in_array($host, ['localhost', 'localhost.localdomain'], true)
                || preg_match('/\\.(?:local|internal|localhost|test|invalid|example|onion)$/i', $host)
            ) {
                return false;
            }

            $addresses = self::resolveAddresses($host);
            if ($addresses === []) {
                return false;
            }
            foreach ($addresses as $address) {
                if (! self::isPublicAddress($address)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Pin the DNS answer validated for this request to the cURL connection.
     * Callers disable redirects so every new URL is checked before connecting.
     *
     * @return array{curl?: array<int, mixed>}
     */
    public static function safeRequestOptions(string $url): array
    {
        $parts = parse_url(trim($url));
        if (! is_array($parts)) {
            throw new \RuntimeException('Invalid outbound URL.');
        }

        $host = strtolower(trim(rtrim((string) ($parts['host'] ?? ''), '[]')));
        if ($host === '' || ! self::isSafe($url)) {
            throw new \RuntimeException('Outbound URL is not a public HTTP or HTTPS destination.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP) || @inet_pton($host) !== false) {
            return [];
        }

        if (! extension_loaded('curl') || ! defined('CURLOPT_RESOLVE')) {
            throw new \RuntimeException('Safe outbound requests require the PHP cURL extension for DNS pinning.');
        }

        $addresses = self::resolveAddresses($host);
        if ($addresses === []) {
            throw new \RuntimeException('Outbound hostname did not resolve to a public address.');
        }
        foreach ($addresses as $address) {
            if (! self::isPublicAddress($address)) {
                throw new \RuntimeException('Outbound hostname resolved to a non-public address.');
            }
        }

        $port = (int) ($parts['port'] ?? (strtolower((string) ($parts['scheme'] ?? '')) === 'https' ? 443 : 80));
        $address = $addresses[0];
        $curlAddress = str_contains($address, ':') ? '['.$address.']' : $address;

        return ['curl' => [CURLOPT_RESOLVE => [$host.':'.$port.':'.$curlAddress]]];
    }

    /**
     * @return list<string>
     */
    private static function resolveAddresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) || @inet_pton($host) !== false) {
            return [$host];
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        $addresses = [];
        foreach ($records ?: [] as $record) {
            if (! empty($record['ip'])) {
                $addresses[] = $record['ip'];
            }
            if (! empty($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($addresses));
    }

    private static function isPublicAddress(string $address): bool
    {
        // Normalize binary addresses so PHP-version-specific FILTER_VALIDATE_IP
        // behavior cannot make an embedded private IPv4 address appear public.
        $packed = @inet_pton($address);
        if (! is_string($packed)) {
            return false;
        }

        if (strlen($packed) === 4) {
            // RFC 6598 shared space is not globally routable and is not
            // consistently excluded by PHP's NO_PRIV_RANGE/NO_RES_RANGE.
            if (self::matchesIpv4Prefix($packed, inet_pton('100.64.0.0'), 10)) {
                return false;
            }

            return filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        if (strlen($packed) !== 16) {
            return false;
        }

        $mappedPrefix = str_repeat("\0", 10)."\xff\xff";
        if (substr($packed, 0, 12) === $mappedPrefix) {
            $ipv4 = inet_ntop(substr($packed, 12));

            return is_string($ipv4) && filter_var(
                $ipv4,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        // Only global-unicast IPv6 (2000::/3) is eligible. Explicitly reject
        // transition, documentation, benchmarking, and ORCHID special ranges.
        if ((ord($packed[0]) & 0xe0) !== 0x20) {
            return false;
        }

        $reservedPrefixes = [
            ['2001::', 32],      // Teredo
            ['2001:2::', 48],    // Benchmarking
            ['2001:10::', 28],   // ORCHID
            ['2001:20::', 28],   // ORCHIDv2
            ['2001:db8::', 32],  // Documentation
            ['2002::', 16],      // 6to4
            ['3fff::', 20],      // Documentation
        ];

        foreach ($reservedPrefixes as [$network, $prefixLength]) {
            $networkPacked = inet_pton($network);
            if (is_string($networkPacked) && self::matchesIpv6Prefix($packed, $networkPacked, $prefixLength)) {
                return false;
            }
        }

        return filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    private static function matchesIpv4Prefix(string $address, string|false $network, int $prefixLength): bool
    {
        if (! is_string($network)) {
            return false;
        }

        $wholeBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;
        if (substr($address, 0, $wholeBytes) !== substr($network, 0, $wholeBytes)) {
            return false;
        }
        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;

        return (ord($address[$wholeBytes]) & $mask) === (ord($network[$wholeBytes]) & $mask);
    }

    private static function matchesIpv6Prefix(string $address, string $network, int $prefixLength): bool
    {
        $wholeBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;

        if (substr($address, 0, $wholeBytes) !== substr($network, 0, $wholeBytes)) {
            return false;
        }

        if ($remainingBits === 0) {
            return true;
        }

        $mask = (0xff << (8 - $remainingBits)) & 0xff;

        return (ord($address[$wholeBytes]) & $mask) === (ord($network[$wholeBytes]) & $mask);
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
                $sourceScheme = strtolower($request->getUri()->getScheme());
                $targetScheme = strtolower($uri->getScheme());

                if (($sourceScheme === 'https' && $targetScheme !== 'https') || ! self::isSafe((string) $uri)) {
                    throw new \RuntimeException('Blocked redirect to an unsafe or downgraded destination.');
                }
            },
        ];
    }

}
