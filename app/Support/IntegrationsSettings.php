<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Config;

class IntegrationsSettings
{
    public static function apply(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $token = trim((string) SiteSetting::get('github_token', ''));
        if ($token !== '') {
            Config::set('services.github.token', $token);
        }
    }

    /**
     * Prefer the token saved under Admin → Integrations (site settings),
     * then config/services.php, then optional .env GITHUB_TOKEN.
     */
    public static function githubToken(): ?string
    {
        try {
            $fromDb = trim((string) SiteSetting::get('github_token', ''));
            if ($fromDb !== '') {
                return $fromDb;
            }
        } catch (\Throwable) {
        }

        $fromConfig = trim((string) (config('services.github.token') ?: ''));
        if ($fromConfig !== '') {
            return $fromConfig;
        }

        $fromEnv = trim((string) (env('GITHUB_TOKEN') ?: ''));

        return $fromEnv !== '' ? $fromEnv : null;
    }
}
