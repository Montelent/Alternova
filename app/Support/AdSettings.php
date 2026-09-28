<?php

namespace App\Support;

use App\Models\SiteSetting;

class AdSettings
{
    public static function enabled(): bool
    {
        return SiteSetting::getBool('ads_enabled', (bool) config('ads.enabled'));
    }

    public static function showPlaceholders(): bool
    {
        return SiteSetting::getBool('ads_show_placeholders', (bool) config('ads.show_placeholders'));
    }

    public static function client(): string
    {
        $fromDb = (string) SiteSetting::get('adsense_client', '');

        return $fromDb !== '' ? $fromDb : (string) config('ads.adsense.client');
    }

    public static function slot(string $name): string
    {
        $key = 'adsense_slot_'.$name;
        $fromDb = (string) SiteSetting::get($key, '');

        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) config('ads.adsense.slots.'.$name, '');
    }

    public static function canRender(string $slot): bool
    {
        return static::enabled()
            && static::client() !== ''
            && static::slot($slot) !== '';
    }
}
