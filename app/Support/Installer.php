<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class Installer
{
    public const LOCK_FILE = 'installed';

    /**
     * Production defaults for shared hosting (Hostinger, etc.).
     * Applied after migrate so sessions/jobs tables exist.
     *
     * @return array<string, string>
     */
    public static function performanceEnvDefaults(): array
    {
        return [
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'database',
            'SESSION_LIFETIME' => '480',
            'SESSION_HTTP_ONLY' => 'true',
            'SESSION_SAME_SITE' => 'lax',
            'QUEUE_CONNECTION' => 'database',
            'SCOUT_DRIVER' => 'collection',
            'FILESYSTEM_DISK' => 'local',
            'BROADCAST_DRIVER' => 'log',
        ];
    }

    public static function lockPath(): string
    {
        return storage_path('app/'.self::LOCK_FILE);
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
            'version' => '1.0.0',
        ], JSON_PRETTY_PRINT));
    }

    public static function unlock(): void
    {
        if (File::exists(self::lockPath())) {
            File::delete(self::lockPath());
        }
    }

    /**
     * Full production-ready .env body. Used when creating or resetting .env.
     *
     * @param  array<string, string>  $overrides
     */
    public static function defaultEnvContent(array $overrides = []): string
    {
        $defaults = [
            'APP_NAME' => 'Alternova',
            'APP_ENV' => 'production',
            'APP_KEY' => '',
            'APP_DEBUG' => 'false',
            'APP_URL' => 'http://localhost',
            'LOG_CHANNEL' => 'stack',
            'LOG_LEVEL' => 'error',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'BROADCAST_DRIVER' => 'log',
            // file session during first boot; applyPerformanceEnv() switches to database after migrate
            'CACHE_DRIVER' => 'file',
            'FILESYSTEM_DISK' => 'local',
            'QUEUE_CONNECTION' => 'database',
            'SESSION_DRIVER' => 'database',
            'SESSION_LIFETIME' => '480',
            'SESSION_HTTP_ONLY' => 'true',
            'SESSION_SAME_SITE' => 'lax',
            'SCOUT_DRIVER' => 'collection',
            'MAIL_MAILER' => 'log',
            'MAIL_FROM_ADDRESS' => 'hello@example.com',
            'MAIL_FROM_NAME' => '"${APP_NAME}"',
        ];

        $values = array_merge($defaults, $overrides);

        if (empty($values['APP_KEY'])) {
            $values['APP_KEY'] = 'base64:'.base64_encode(random_bytes(32));
        }

        $lines = [
            'APP_NAME='.self::envValue($values['APP_NAME']),
            'APP_ENV='.$values['APP_ENV'],
            'APP_KEY='.$values['APP_KEY'],
            'APP_DEBUG='.$values['APP_DEBUG'],
            'APP_URL='.$values['APP_URL'],
            '',
            'LOG_CHANNEL='.$values['LOG_CHANNEL'],
            'LOG_LEVEL='.$values['LOG_LEVEL'],
            '',
            'DB_CONNECTION='.$values['DB_CONNECTION'],
            'DB_HOST='.$values['DB_HOST'],
            'DB_PORT='.$values['DB_PORT'],
            'DB_DATABASE='.self::envValue($values['DB_DATABASE']),
            'DB_USERNAME='.self::envValue($values['DB_USERNAME']),
            'DB_PASSWORD='.self::envValue($values['DB_PASSWORD']),
            '',
            'BROADCAST_DRIVER='.$values['BROADCAST_DRIVER'],
            'CACHE_DRIVER='.$values['CACHE_DRIVER'],
            'FILESYSTEM_DISK='.$values['FILESYSTEM_DISK'],
            'QUEUE_CONNECTION='.$values['QUEUE_CONNECTION'],
            'SESSION_DRIVER='.$values['SESSION_DRIVER'],
            'SESSION_LIFETIME='.$values['SESSION_LIFETIME'],
            'SESSION_HTTP_ONLY='.$values['SESSION_HTTP_ONLY'],
            'SESSION_SAME_SITE='.$values['SESSION_SAME_SITE'],
            '',
            'SCOUT_DRIVER='.$values['SCOUT_DRIVER'],
            '',
            'MAIL_MAILER='.$values['MAIL_MAILER'],
            'MAIL_FROM_ADDRESS='.$values['MAIL_FROM_ADDRESS'],
            'MAIL_FROM_NAME='.$values['MAIL_FROM_NAME'],
        ];

        return implode("\n", $lines)."\n";
    }

    protected static function envValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'|=/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }

    /**
     * Create a complete .env if missing or incomplete (missing DB_CONNECTION).
     */
    public static function ensureEnvFile(): bool
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            $url = self::detectAppUrl();
            File::put($envPath, self::defaultEnvContent([
                'APP_URL' => $url,
            ]));

            return true;
        }

        $content = File::get($envPath);

        if (! preg_match('/^DB_CONNECTION=/m', $content)) {
            $key = '';
            if (preg_match('/^APP_KEY=(.+)$/m', $content, $m)) {
                $key = trim($m[1]);
            }

            File::put($envPath, self::defaultEnvContent([
                'APP_KEY' => $key,
                'APP_URL' => self::detectAppUrl(),
            ]));
        }

        return true;
    }

    public static function detectAppUrl(): string
    {
        if (! empty($_SERVER['HTTP_HOST'])) {
            $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

            return $scheme.'://'.$_SERVER['HTTP_HOST'];
        }

        return 'http://localhost';
    }

    /**
     * @param  array<string, string>  $values
     */
    public static function writeEnv(array $values): void
    {
        self::ensureEnvFile();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        // Safe shared-hosting baseline (may be overridden by $values)
        $values = array_merge([
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'database',
            'SESSION_LIFETIME' => '480',
            'QUEUE_CONNECTION' => 'database',
            'SCOUT_DRIVER' => 'collection',
            'MAIL_MAILER' => 'log',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
        ], $values);

        foreach ($values as $key => $value) {
            $value = (string) $value;
            $formatted = self::envValue($value);

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$formatted}",
                    $content
                );
            } else {
                $content = rtrim($content)."\n{$key}={$formatted}\n";
            }
        }

        File::put($envPath, $content);
    }

    /**
     * Write CACHE/SESSION/QUEUE defaults after migrations (tables exist).
     * Safe to call multiple times.
     */
    public static function applyPerformanceEnv(): void
    {
        self::writeEnv(self::performanceEnvDefaults());

        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
        }
    }

    public static function ensureAppKey(): string
    {
        self::ensureEnvFile();

        $envPath = base_path('.env');
        $content = File::get($envPath);

        if (preg_match('/^APP_KEY=(.+)$/m', $content, $m) && trim($m[1]) !== '') {
            return trim($m[1]);
        }

        $key = 'base64:'.base64_encode(random_bytes(32));
        self::writeEnv(['APP_KEY' => $key]);

        return $key;
    }

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
                'value' => is_writable(storage_path()) ? 'yes' : 'no',
            ],
            'bootstrap_cache_writable' => [
                'label' => 'bootstrap/cache writable',
                'ok' => is_writable(base_path('bootstrap/cache')),
                'value' => is_writable(base_path('bootstrap/cache')) ? 'yes' : 'no',
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
                $failed[] = $check['label'].' ('.$check['value'].')';
            }
        }

        return $failed;
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
