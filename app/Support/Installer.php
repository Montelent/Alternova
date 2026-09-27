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

    /**
     * Try to create required storage directories.
     */
    public static function ensureStorageDirectories(): void
    {
        $dirs = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }
    }

    public static function requirements(): array
    {
        self::ensureStorageDirectories();

        return [
            'php_version' => [
                'label' => 'PHP >= 8.2',
                'ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'value' => PHP_VERSION,
            ],
            'pdo' => [
                'label' => 'PDO MySQL or PostgreSQL',
                'ok' => extension_loaded('pdo_mysql') || extension_loaded('pdo_pgsql'),
                'value' => extension_loaded('pdo_mysql')
                    ? 'pdo_mysql'
                    : (extension_loaded('pdo_pgsql') ? 'pdo_pgsql' : 'missing'),
            ],
            'openssl' => [
                'label' => 'OpenSSL',
                'ok' => extension_loaded('openssl'),
                'value' => extension_loaded('openssl') ? 'ok' : 'missing',
            ],
            'mbstring' => [
                'label' => 'Mbstring',
                'ok' => extension_loaded('mbstring'),
                'value' => extension_loaded('mbstring') ? 'ok' : 'missing',
            ],
            'tokenizer' => [
                'label' => 'Tokenizer',
                'ok' => extension_loaded('tokenizer'),
                'value' => extension_loaded('tokenizer') ? 'ok' : 'missing',
            ],
            'json' => [
                'label' => 'JSON',
                'ok' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'ok' : 'missing',
            ],
            'storage_writable' => [
                'label' => 'storage/ writable',
                'ok' => is_writable(storage_path()),
                'value' => is_writable(storage_path()) ? 'yes' : 'no — run: chmod -R 775 storage',
            ],
            'bootstrap_cache_writable' => [
                'label' => 'bootstrap/cache writable',
                'ok' => is_writable(base_path('bootstrap/cache')),
                'value' => is_writable(base_path('bootstrap/cache')) ? 'yes' : 'no — run: chmod -R 775 bootstrap/cache',
            ],
            'env_example' => [
                'label' => '.env.example present',
                'ok' => File::exists(base_path('.env.example')),
                'value' => File::exists(base_path('.env.example')) ? 'yes' : 'missing',
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

    public static function failedRequirementLabels(): array
    {
        $failed = [];
        foreach (self::requirements() as $check) {
            if (! $check['ok']) {
                $failed[] = $check['label'] . ' (' . $check['value'] . ')';
            }
        }

        return $failed;
    }

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
     * @param  array<string, string>  $values
     */
    public static function writeEnv(array $values): void
    {
        self::ensureEnvFile();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $value = (string) $value;

            if ($value !== '' && ! preg_match('/^[A-Za-z0-9_.\-\/:]+$/', $value)) {
                $value = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
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
