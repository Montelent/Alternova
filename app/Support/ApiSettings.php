<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Global public API switches (any domain / install).
 * Stored in site_settings so no env edit is required.
 */
class ApiSettings
{
    public static function defaults(): array
    {
        return [
            'api_globally_enabled' => true,
            'api_require_key' => false,
            'api_rate_limit_anon' => 60,
            'api_rate_limit_key' => 600,
        ];
    }

    public static function isGloballyEnabled(): bool
    {
        return SiteSetting::getBool('api_globally_enabled', true);
    }

    public static function requireKey(): bool
    {
        return SiteSetting::getBool('api_require_key', false);
    }

    public static function rateLimitAnonymous(): int
    {
        $n = (int) SiteSetting::get('api_rate_limit_anon', 60);

        return max(10, min(1000, $n > 0 ? $n : 60));
    }

    public static function rateLimitAuthenticated(): int
    {
        $n = (int) SiteSetting::get('api_rate_limit_key', 600);

        return max(30, min(5000, $n > 0 ? $n : 600));
    }

    public static function setGloballyEnabled(bool $on): void
    {
        SiteSetting::set('api_globally_enabled', $on ? '1' : '0');
    }

    public static function setRequireKey(bool $on): void
    {
        SiteSetting::set('api_require_key', $on ? '1' : '0');
    }

    public static function setRateLimits(int $anon, int $key): void
    {
        SiteSetting::set('api_rate_limit_anon', (string) max(10, min(1000, $anon)));
        SiteSetting::set('api_rate_limit_key', (string) max(30, min(5000, $key)));
    }
}
