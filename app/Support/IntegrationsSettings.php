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

    public static function githubToken(): ?string
    {
        $token = trim((string) (config('services.github.token') ?: SiteSetting::get('github_token', '')));

        return $token !== '' ? $token : null;
    }
}
