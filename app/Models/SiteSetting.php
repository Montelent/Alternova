<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    private const ENCRYPTED_PREFIX = 'enc:v1:';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();
        $value = $all[$key] ?? $default;

        if (! static::isSensitiveKey($key) || ! is_string($value) || ! str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(self::ENCRYPTED_PREFIX)));
        } catch (\Throwable $e) {
            // A legacy plaintext secret may coincidentally begin with the
            // marker. Preserve it rather than breaking the integration.
            Log::warning('Sensitive site setting could not be decrypted; treating as legacy plaintext.', ['key' => $key]);

            return $value;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => static::encodeValue($key, $value)]
        );

        static::clearCache();
    }

    /**
     * @param  array<string, mixed>  $pairs
     */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => static::encodeValue((string) $key, $value)]
            );
        }

        static::clearCache();
    }

    protected static function encodeValue(string $key, mixed $value): string
    {
        $plain = is_bool($value) ? ($value ? '1' : '0') : (string) ($value ?? '');

        if (! static::isSensitiveKey($key) || $plain === '') {
            return $plain;
        }

        if (str_starts_with($plain, self::ENCRYPTED_PREFIX)) {
            try {
                Crypt::decryptString(substr($plain, strlen(self::ENCRYPTED_PREFIX)));

                return $plain;
            } catch (\Throwable) {
                // Treat a literal user secret beginning with the marker as plaintext.
            }
        }

        return self::ENCRYPTED_PREFIX.Crypt::encryptString($plain);
    }

    protected static function isSensitiveKey(string $key): bool
    {
        return in_array($key, ['github_token', 'mail_smtp_password', 'mail_resend_key'], true)
            || preg_match('/(?:token|secret|password|(?:api|resend|access|private|secret)[_-]?key)$/i', $key) === 1;
    }

    protected static function clearCache(): void
    {
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
