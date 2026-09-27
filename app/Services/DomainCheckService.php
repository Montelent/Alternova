<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DomainCheckService
{
    public const CACHE_TTL = 86400; // 24 hours

    /**
     * Check domain availability.
     * Fast path: DNS records. Fallback: RDAP.
     *
     * @return array{status: string, method: string, cached: bool}
     */
    public function check(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $cacheKey = "domain_check:{$domain}";

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            return array_merge($cached, ['cached' => true]);
        }

        // Tier 1: Fast DNS check
        $dnsStatus = $this->checkViaDns($domain);

        if ($dnsStatus === 'taken') {
            $result = ['status' => 'taken', 'method' => 'dns', 'cached' => false];
            Cache::put($cacheKey, $result, self::CACHE_TTL);
            return $result;
        }

        // Tier 2: RDAP fallback for higher confidence
        $rdapStatus = $this->checkViaRdap($domain);

        $result = [
            'status' => $rdapStatus,
            'method' => 'rdap',
            'cached' => false,
        ];

        Cache::put($cacheKey, $result, self::CACHE_TTL);

        return $result;
    }

    protected function checkViaDns(string $domain): string
    {
        // Check for A, AAAA, or NS records
        $records = @dns_get_record($domain, DNS_A + DNS_AAAA + DNS_NS);

        if ($records === false || empty($records)) {
            return 'available'; // or unknown – treat as potentially available
        }

        return 'taken';
    }

    protected function checkViaRdap(string $domain): string
    {
        // Public RDAP bootstrap / common endpoints
        // For .com/.net we can use Verisign or a public proxy
        $tld = substr(strrchr($domain, '.'), 1);

        $rdapUrls = [
            'com' => "https://rdap.verisign.com/com/v1/domain/{$domain}",
            'net' => "https://rdap.verisign.com/net/v1/domain/{$domain}",
            'org' => "https://rdap.publicinterestregistry.org/rdap/domain/{$domain}",
            'io' => "https://rdap.nic.io/domain/{$domain}",
            'dev' => "https://rdap.nic.google/domain/{$domain}",
            'app' => "https://rdap.nic.google/domain/{$domain}",
        ];

        $url = $rdapUrls[$tld] ?? "https://rdap.org/domain/{$domain}";

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Accept' => 'application/rdap+json'])
                ->get($url);

            if ($response->status() === 404) {
                return 'available';
            }

            if ($response->successful()) {
                return 'taken';
            }

            // Rate limited or error – fall back to DNS result
            return 'unknown';
        } catch (\Throwable $e) {
            Log::debug("RDAP check failed for {$domain}: " . $e->getMessage());
            return 'unknown';
        }
    }

    /**
     * Generate affiliate registration links.
     */
    public function affiliateLinks(string $domain): array
    {
        $encoded = urlencode($domain);

        return [
            'namecheap' => "https://www.namecheap.com/domains/registration/results/?domain={$encoded}&aff=YOUR_AFFILIATE_ID",
            'porkbun' => "https://porkbun.com/checkout/search?q={$encoded}&aff=YOUR_AFFILIATE_ID",
            'godaddy' => "https://www.godaddy.com/domainsearch/find?domainToCheck={$encoded}&isc=YOUR_AFFILIATE_ID",
        ];
    }
}
