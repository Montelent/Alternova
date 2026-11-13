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
                    if ($value === '' || str_starts_with($value, self::PREFIX)) {
                        continue;
                    }

                    DB::table('site_settings')
                        ->where('id', $setting->id)
                        ->update(['value' => self::PREFIX.Crypt::encryptString($value)]);
                }
            });

        try {
            Cache::forget('site_settings');
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

                    DB::table('site_settings')
                        ->where('id', $setting->id)
                        ->update(['value' => Crypt::decryptString(substr($value, strlen(self::PREFIX)))]);
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
