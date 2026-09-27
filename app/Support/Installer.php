<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

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
        return [
            'php_version' => [
                'label' => 'PHP >= 8.3',
                'ok' => version_compare(PHP_VERSION, '8.3.0', '>='),
                'value' => PHP_VERSION,
            ],
            'pdo_pgsql' => [
                'label' => 'PDO PostgreSQL (or PDO MySQL)',
                'ok' => extension_loaded('pdo_pgsql') || extension_loaded('pdo_mysql'),
                'value' => extension_loaded('pdo_pgsql')
                    ? 'pdo_pgsql'
                    : (extension_loaded('pdo_mysql') ? 'pdo_mysql' : 'missing'),
            ],
            'openssl' => [
                'label' => 'OpenSSL extension',
                'ok' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'loaded' : 'missing',
            ],
            'mbstring' => [
                'label' => 'Mbstring extension',
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'loaded' : 'missing',
            ],
            'tokenizer' => [
                'label' => 'Tokenizer extension',
                'ok' => extension_loaded('tokenizer'),
                'value' => extension_loaded('tokenizer') ? 'loaded' : 'missing',
            ],
            'xml' => [
                'label' => 'XML extension',
                'ok' => extension_loaded('xml'),
                'value' => extension_loaded('xml') ? 'loaded' : 'missing',
            ],
            'ctype' => [
                'label' => 'Ctype extension',
                'ok' => extension_loaded('ctype'),
                'value' => extension_loaded('ctype') ? 'loaded' : 'missing',
            ],
            'json' => [
                'label' => 'JSON extension',
                'ok' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'loaded' : 'missing',
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
            'env_example' => [
                'label' => '.env.example present',
                'ok' => File::exists(base_path('.env.example')),
                'value' => File::exists(base_path('.env.example')) ? 'yes' : 'no',
            ],
        ];
    }

    public static function allRequirementsMet(): bool
    {
        foreach (self::requirements() as $check) {
            if (! $check['ok']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensure .env exists (copy from .env.example if needed).
     */
    public static function ensureEnvFile(): bool
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        if (File::exists($envPath)) {
            return true;
        }

        if (! File::exists($examplePath)) {
            return false;
        }

        File::copy($examplePath, $envPath);

        return File::exists($envPath);
    }

    /**
     * Write key=value pairs into .env (create file from example if missing).
     *
     * @param  array<string, string>  $values
     */
    public static function writeEnv(array $values): void
    {
        self::ensureEnvFile();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $value = (string) $value;

            // Quote values that contain spaces or special chars
            if ($value !== '' && ! preg_match('/^[A-Za-z0-9_.\-]+$/', $value)) {
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            }

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $content
                );
            } else {
                $content .= PHP_EOL . "{$key}={$value}";
            }
        }

        File::put($envPath, $content);
    }

    /**
     * Generate APP_KEY if missing and write it to .env.
     */
    public static function ensureAppKey(): string
    {
        self::ensureEnvFile();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        if (preg_match('/^APP_KEY=(.+)$/m', $content, $m) && trim($m[1]) !== '') {
            return trim($m[1]);
        }

        $key = 'base64:' . base64_encode(random_bytes(32));
        self::writeEnv(['APP_KEY' => $key]);

        return $key;
    }

    /**
     * Test database connection with the given credentials (without relying on current config).
     */
    public static function testDatabaseConnection(
        string $connection,
        string $host,
        string $port,
        string $database,
        string $username,
        string $password
    ): array {
        try {
            if ($connection === 'pgsql') {
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
            } else {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            }

            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);

            $pdo->query('SELECT 1');

            return ['success' => true, 'message' => 'Connection successful.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
