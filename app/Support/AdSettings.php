<?php

namespace App\Support;

use App\Models\SiteSetting;

class AdSettings
{
    /**
     * Placement keys used across the public site.
     *
     * @return list<string>
     */
    public static function placements(): array
    {
        return [
            'header',
            'homepage',
            'after_hero',
            'in_article',
            'sidebar',
            'between_list',
            'alternative_mid',
            'tool_mid',
            'domain_page',
            'compare_page',
            'browse_hub',
            'before_footer',
            'footer',
            'sticky_mobile',
        ];
    }

    public static function placementLabels(): array
    {
        return [
            'header' => 'Below site header (all pages)',
            'homepage' => 'Homepage (mid page)',
            'after_hero' => 'After page hero / title',
            'in_article' => 'In-article (inside long content)',
            'sidebar' => 'Sidebar column',
            'between_list' => 'Between listing cards / grids',
            'alternative_mid' => 'Open-source alternative detail (mid)',
            'tool_mid' => 'Proprietary tool / alternativesto page (mid)',
            'domain_page' => 'Domain Combinator page',
            'compare_page' => 'Compare alternatives page',
            'browse_hub' => 'Browse hubs (categories / languages / licenses)',
            'before_footer' => 'Above site footer',
            'footer' => 'Inside footer area',
            'sticky_mobile' => 'Sticky bottom bar (mobile only)',
        ];
    }

    public static function enabled(): bool
    {
        return SiteSetting::getBool('ads_enabled', (bool) config('ads.enabled'));
    }

    public static function showPlaceholders(): bool
    {
        return SiteSetting::getBool('ads_show_placeholders', (bool) config('ads.show_placeholders'));
    }

    /** Global <head> script block (any network). */
    public static function headCode(): string
    {
        return trim((string) SiteSetting::get('ads_head_code', ''));
    }

    /** Optional AdSense publisher ID for convenience slots. */
    public static function client(): string
    {
        $fromDb = trim((string) SiteSetting::get('adsense_client', ''));

        return $fromDb !== '' ? $fromDb : (string) config('ads.adsense.client');
    }

    /** Full HTML/JS for a placement (any network). */
    public static function code(string $placement): string
    {
        return trim((string) SiteSetting::get('ads_code_'.$placement, ''));
    }

    /** Legacy / optional AdSense numeric slot ID. */
    public static function slot(string $name): string
    {
        $fromDb = trim((string) SiteSetting::get('adsense_slot_'.$name, ''));

        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) config('ads.adsense.slots.'.$name, '');
    }

    public static function canRender(string $placement): bool
    {
        if (! static::enabled()) {
            return false;
        }

        if (static::code($placement) !== '') {
            return true;
        }

        // AdSense fallback: needs client + slot ID
        return static::client() !== '' && static::slot($placement) !== '';
    }

    public static function shouldLoadAdsenseScript(): bool
    {
        if (! static::enabled() || static::client() === '') {
            return false;
        }

        // Load script if any placement still uses AdSense slot IDs without custom HTML
        foreach (static::placements() as $p) {
            if (static::code($p) === '' && static::slot($p) !== '') {
                return true;
            }
        }

        return false;
    }
}
