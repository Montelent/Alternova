<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Configurable Filament admin path (default: admin → /admin).
 */
class AdminPanel
{
    public static function path(): string
    {
        $path = 'admin';

        try {
            if (Schema::hasTable('site_settings')) {
                $stored = trim((string) SiteSetting::get('admin_path', 'admin'));
                if ($stored !== '') {
                    $path = $stored;
                }
            }
        } catch (\Throwable) {
        }

        $path = strtolower(trim($path, " \t\n\r\0\x0B/"));
        $path = preg_replace('/[^a-z0-9\-_]/', '', $path) ?? '';

        if ($path === '' || in_array($path, ['login', 'register', 'api', 'livewire', 'up', 'install'], true)) {
            return 'admin';
        }

        return $path;
    }

    public static function url(string $suffix = ''): string
    {
        $base = '/'.self::path();
        $suffix = ltrim($suffix, '/');

        return $suffix === '' ? url($base) : url($base.'/'.$suffix);
    }
}
