<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PREFIX = 'enc:v1:';

    public function up(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')
            ->select(['id', 'key', 'value'])
            ->whereNotNull('value')
            ->orderBy('id')
            ->chunkById(100, function ($settings): void {
                foreach ($settings as $setting) {
                    if (! $this->isSensitive((string) $setting->key)) {
                        continue;
                    }

                    $value = (string) $setting->value;
                    if ($value === '') {
                        continue;
                    }

                    if (str_starts_with($value, self::PREFIX)) {
                        try {
                            Crypt::decryptString(substr($value, strlen(self::PREFIX)));
                            continue;
                        } catch (\Throwable) {
                            // Legacy plaintext may coincidentally begin with the marker.
                        }
                    }

                    DB::table('site_settings')
                        ->where('id', $setting->id)
                        ->update(['value' => self::PREFIX.Crypt::encryptString($value)]);
                }
            });

        try {
            try {
            Cache::forget('site_settings');
        } catch (\Throwable) {
        }
        } catch (\Throwable) {
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_settings')) {
            return;
        }

        DB::table('site_settings')
            ->select(['id', 'key', 'value'])
            ->whereNotNull('value')
            ->orderBy('id')
            ->chunkById(100, function ($settings): void {
                foreach ($settings as $setting) {
                    if (! $this->isSensitive((string) $setting->key)) {
                        continue;
                    }

                    $value = (string) $setting->value;
                    if (! str_starts_with($value, self::PREFIX)) {
                        continue;
                    }

                    try {
                        $plain = Crypt::decryptString(substr($value, strlen(self::PREFIX)));
                    } catch (\Throwable) {
                        continue;
                    }

                    DB::table('site_settings')
                        ->where('id', $setting->id)
                        ->update(['value' => $plain]);
                }
            });

        Cache::forget('site_settings');
    }

    private function isSensitive(string $key): bool
    {
        return in_array($key, ['github_token', 'mail_smtp_password', 'mail_resend_key'], true)
            || preg_match('/(?:token|secret|password|(?:api|resend|access|private|secret)[_-]?key)$/i', $key) === 1;
    }
};
