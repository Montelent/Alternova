<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainCheckService
{
    /**
     * Fast availability check: DNS first, optional short RDAP, 24h cache.
     *
     * @return array{domain: string, available: bool|null, status: string, checked_at: string}
     */
    public function check(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $cacheKey = 'domain_check:v3:'.$domain;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($domain) {
            if ($this->hasDnsRecords($domain)) {
                return [
                    'domain' => $domain,
                    'available' => false,
                    'status' => 'taken',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            // Short RDAP only — never block the UI for long
            $rdap = $this->checkRdap($domain);

            if ($rdap === true) {
                return [
                    'domain' => $domain,
                    'available' => false,
                    'status' => 'taken',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            return [
                'domain' => $domain,
                'available' => true,
                'status' => 'available',
                'checked_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * @param  list<string>  $domains
     * @return array<string, array{domain: string, available: bool|null, status: string, checked_at: string}>
     */
    public function checkMany(array $domains): array
    {
        $out = [];
        foreach ($domains as $domain) {
            try {
                $out[$domain] = $this->check($domain);
            } catch (\Throwable $e) {
                Log::debug('Domain check error '.$domain.': '.$e->getMessage());
                $out[$domain] = [
                    'domain' => $domain,
                    'available' => null,
                    'status' => 'unknown',
                    'checked_at' => now()->toIso8601String(),
                ];
            }
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function affiliateLinks(string $domain): array
    {
        $domain = strtolower(trim($domain));

        return [
            'namecheap' => url('/go/namecheap?domain='.rawurlencode($domain)),
            'porkbun' => url('/go/porkbun?domain='.rawurlencode($domain)),
            'godaddy' => url('/go/godaddy?domain='.rawurlencode($domain)),
        ];
    }

    protected function hasDnsRecords(string $domain): bool
    {
        try {
            $ip = @gethostbyname($domain);
            if ($ip && $ip !== $domain && filter_var($ip, FILTER_VALIDATE_IP)) {
                return true;
            }

            // Only A/NS — skip slow multi-type scans
            if (function_exists('dns_get_record')) {
                $a = @dns_get_record($domain, DNS_A);
                if (! empty($a)) {
                    return true;
                }
                $ns = @dns_get_record($domain, DNS_NS);
                if (! empty($ns)) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('DNS check error for '.$domain.': '.$e->getMessage());
        }

        return false;
    }

    /**
     * @return bool|null true = registered, false = not found, null = unknown
     */
    protected function checkRdap(string $domain): ?bool
    {
        $tld = $this->extractTld($domain);
        $endpoints = $this->rdapEndpoints($tld);

        // One endpoint only, very short timeout for shared hosting
        $base = $endpoints[0] ?? null;
        if (! $base) {
            return null;
        }

        try {
            $url = rtrim($base, '/').'/domain/'.$domain;
            $response = Http::timeout(1.8)
                ->connectTimeout(1.2)
                ->withHeaders(['Accept' => 'application/rdap+json, application/json'])
                ->get($url);

            if ($response->status() === 404) {
                return false;
            }

            if ($response->successful()) {
                $body = $response->json();
                if (is_array($body) && (
                    isset($body['objectClassName'])
                    || isset($body['ldhName'])
                    || isset($body['handle'])
                )) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            Log::debug('RDAP check failed for '.$domain.': '.$e->getMessage());
        }

        return null;
    }

    protected function extractTld(string $domain): string
    {
        $parts = explode('.', $domain);

        return strtolower(end($parts) ?: '');
    }

    /** @return list<string> */
    protected function rdapEndpoints(string $tld): array
    {
        $map = [
            'com' => ['https://rdap.verisign.com/com/v1'],
            'net' => ['https://rdap.verisign.com/net/v1'],
            'org' => ['https://rdap.publicinterestregistry.org/rdap'],
            'io' => ['https://rdap.nic.io'],
            'dev' => ['https://rdap.nic.google'],
            'app' => ['https://rdap.nic.google'],
            'co' => ['https://rdap.nic.co'],
            'ai' => ['https://rdap.nic.ai'],
        ];

        return $map[$tld] ?? ['https://rdap.org'];
    }
}
