<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainCheckService
{
    /**
     * Check domain availability.
     *
     * @return array{domain: string, available: bool|null, status: string, checked_at: string}
     */
    public function check(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $cacheKey = 'domain_check:' . $domain;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($domain) {
            $hasDns = $this->hasDnsRecords($domain);

            if ($hasDns) {
                return [
                    'domain' => $domain,
                    'available' => false,
                    'status' => 'taken',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            $rdap = $this->checkRdap($domain);

            if ($rdap === true) {
                return [
                    'domain' => $domain,
                    'available' => false,
                    'status' => 'taken',
                    'checked_at' => now()->toIso8601String(),
                ];
            }

            if ($rdap === false) {
                return [
                    'domain' => $domain,
                    'available' => true,
                    'status' => 'available',
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
     * Build registrar affiliate / search links for a domain.
     *
     * @return array<string, string>
     */
    public function affiliateLinks(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $encoded = rawurlencode($domain);

        return [
            'namecheap' => 'https://www.namecheap.com/domains/registration/results/?domain=' . $encoded,
            'porkbun' => 'https://porkbun.com/checkout/search?q=' . $encoded,
            'godaddy' => 'https://www.godaddy.com/domainsearch/find?domainToCheck=' . $encoded,
        ];
    }

    protected function hasDnsRecords(string $domain): bool
    {
        try {
            $types = ['A', 'AAAA', 'NS', 'CNAME', 'MX'];
            foreach ($types as $type) {
                $records = @dns_get_record($domain, constant('DNS_' . $type));
                if (! empty($records)) {
                    return true;
                }
            }

            $ip = @gethostbyname($domain);
            if ($ip && $ip !== $domain && filter_var($ip, FILTER_VALIDATE_IP)) {
                return true;
            }
        } catch (\Throwable $e) {
            Log::debug('DNS check error for ' . $domain . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @return bool|null true = registered, false = not found, null = unknown/error
     */
    protected function checkRdap(string $domain): ?bool
    {
        $tld = $this->extractTld($domain);
        $endpoints = $this->rdapEndpoints($tld);

        foreach ($endpoints as $base) {
            try {
                $url = rtrim($base, '/') . '/domain/' . $domain;
                $response = Http::timeout(4)
                    ->connectTimeout(3)
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
                Log::debug('RDAP check failed for ' . $domain . ': ' . $e->getMessage());
            }
        }

        return null;
    }

    protected function extractTld(string $domain): string
    {
        $parts = explode('.', $domain);

        return strtolower(end($parts) ?: '');
    }

    /**
     * @return list<string>
     */
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

        $list = $map[$tld] ?? [];
        $list[] = 'https://rdap.org';

        return $list;
    }
}
