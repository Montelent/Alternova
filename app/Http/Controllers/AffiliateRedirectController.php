<?php

namespace App\Http\Controllers;

use App\Models\AffiliateClick;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AffiliateRedirectController extends Controller
{
    private const PROVIDERS = ['namecheap', 'porkbun', 'godaddy'];

    public function __invoke(Request $request, string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $domain = strtolower(trim((string) $request->query('domain', '')));
        abort_unless($domain !== '' && preg_match('/^[a-z0-9][a-z0-9.-]{0,250}\.[a-z]{2,24}$/i', $domain), 422);

        $url = $this->buildDestination($provider, $domain);

        try {
            if (Schema::hasTable('affiliate_clicks')) {
                AffiliateClick::query()->create([
                    'provider' => $provider,
                    'domain' => $domain,
                    'destination_url' => $url,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                    'referer' => substr((string) $request->headers->get('referer'), 0, 500),
                ]);
            }
        } catch (\Throwable) {
        }

        return redirect()->away($url, 302);
    }

    protected function buildDestination(string $provider, string $domain): string
    {
        $encoded = rawurlencode($domain);

        // Optional affiliate / tracking params from site settings
        $nc = trim((string) SiteSetting::get('affiliate_namecheap', ''));
        $pb = trim((string) SiteSetting::get('affiliate_porkbun', ''));
        $gd = trim((string) SiteSetting::get('affiliate_godaddy', ''));

        return match ($provider) {
            'namecheap' => 'https://www.namecheap.com/domains/registration/results/?domain='.$encoded
                .($nc !== '' ? '&aff='.rawurlencode($nc) : ''),
            'porkbun' => 'https://porkbun.com/checkout/search?q='.$encoded
                .($pb !== '' ? '&coupon='.rawurlencode($pb) : ''),
            'godaddy' => 'https://www.godaddy.com/domainsearch/find?domainToCheck='.$encoded
                .($gd !== '' ? '&isc='.rawurlencode($gd) : ''),
            default => 'https://www.google.com/search?q='.$encoded,
        };
    }
}
