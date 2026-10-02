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

    public static function setGloballyEnabled(bool $on): void
    {
        SiteSetting::set('api_globally_enabled', $on ? '1' : '0');
    }

    public static function setRequireKey(bool $on): void
    {
        SiteSetting::set('api_require_key', $on ? '1' : '0');
    }
}
