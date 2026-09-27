<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class Installer
{
    public const LOCK_FILE = 'installed';

    public static function lockPath(): string
    {
        return storage_path('app/' . self::LOCK_FILE);
    }

    public static function isInstalled(): bool
    {
        return File::exists(self::lockPath());
    }

    public static function lock(): void
    {
        File::ensureDirectoryExists(dirname(self::lockPath()));
        File::put(self::lockPath(), json_encode([
            'installed_at' => now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ], JSON_PRETTY_PRINT));
    }

    public static function unlock(): void
    {
        if (File::exists(self::lockPath())) {
            File::delete(self::lockPath());
        }
    }

    /**
     * Run a safe subset of artisan commands and return captured output.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function runArtisan(string $command, array $parameters = []): array
    {
        $exitCode = Artisan::call($command, $parameters);

        return [
            'command' => $command,
            'exit_code' => $exitCode,
            'output' => Artisan::output(),
            'success' => $exitCode === 0,
        ];
    }

    public static function requirements(): array
    {
        $checks = [
            'php_version' => [
                'label' => 'PHP >= 8.3',
                'ok' => version_compare(PHP_VERSION, '8.3.0', '>='),
                'value' => PHP_VERSION,
            ],
            'pdo_pgsql' => [
                'label' => 'PDO PostgreSQL extension',
                'ok' => extension_loaded('pdo_pgsql'),
                'value' => extension_loaded('pdo_pgsql') ? 'loaded' : 'missing',
            ],
            'redis' => [
                'label' => 'Redis extension (recommended)',
                'ok' => extension_loaded('redis') || extension_loaded('phpredis'),
                'value' => extension_loaded('redis') || extension_loaded('phpredis') ? 'loaded' : 'missing',
            ],
            'storage_writable' => [
                'label' => 'storage/ writable',
                'ok' => is_writable(storage_path()),
                'value' => is_writable(storage_path()) ? 'yes' : 'no',
            ],
            'bootstrap_cache_writable' => [
                'label' => 'bootstrap/cache writable',
                'ok' => is_writable(base_path('bootstrap/cache')),
                'value' => is_writable(base_path('bootstrap/cache')) ? 'yes' : 'no',
            ],
            'env_exists' => [
                'label' => '.env file present',
                'ok' => File::exists(base_path('.env')),
                'value' => File::exists(base_path('.env')) ? 'yes' : 'no',
            ],
            'app_key' => [
                'label' => 'APP_KEY set',
                'ok' => ! empty(config('app.key')),
                'value' => ! empty(config('app.key')) ? 'set' : 'missing',
            ],
        ];

        return $checks;
    }

    public static function allRequirementsMet(): bool
    {
        foreach (self::requirements() as $check) {
            // Redis is recommended but not hard-required for install
            if (($check['label'] ?? '') === 'Redis extension (recommended)') {
                continue;
            }
            if (! $check['ok']) {
                return false;
            }
        }

        return true;
    }
}
