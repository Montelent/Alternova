<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        return $all[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
        );

        try {
            Cache::forget('site_settings');
        } catch (\Throwable) {
        }
    }

    /**
     * @param  array<string, mixed>  $pairs
     */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '')]
            );
        }

        try {
            Cache::forget('site_settings');
        } catch (\Throwable) {
        }
    }

    /**
     * Never fatal during install: the database cache table does not exist yet.
     *
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        try {
            return Cache::remember('site_settings', 300, function () {
                return static::query()->pluck('value', 'key')->toArray();
            });
        } catch (\Throwable) {
            try {
                return static::query()->pluck('value', 'key')->toArray();
            } catch (\Throwable) {
                return [];
            }
        }
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }
}
