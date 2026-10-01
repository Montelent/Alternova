<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Scheduled-job config for any domain/host. Paths and PHP binary are
 * detected from the current install (base_path, PHP_BINARY) — never hardcoded.
 */
class CronSettings
{
    public static function defaults(): array
    {
        return [
            'cron_metrics_enabled' => true,
            'cron_metrics_time' => '03:15',
            'cron_metrics_limit' => 25,
            'cron_links_enabled' => true,
            'cron_links_day' => 1,
            'cron_links_time' => '04:00',
            'cron_links_limit' => 40,
            'cron_digest_enabled' => true,
            'cron_digest_day' => 1,
            'cron_digest_time' => '09:00',
            'cron_notif_digest_enabled' => true,
            'cron_notif_digest_day' => 1,
            'cron_notif_digest_time' => '09:30',
            'cron_expire_sponsored_enabled' => true,
            'cron_expire_sponsored_time' => '00:30',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $defaults = self::defaults();

        if (str_ends_with($key, '_enabled')) {
            return SiteSetting::getBool($key, (bool) ($defaults[$key] ?? $default ?? true));
        }

        if (str_ends_with($key, '_limit') || str_ends_with($key, '_day')) {
            return (int) SiteSetting::get($key, $defaults[$key] ?? $default ?? 0);
        }

        return SiteSetting::get($key, $defaults[$key] ?? $default);
    }

    public static function markScheduleRan(): void
    {
        try {
            SiteSetting::set('cron_last_schedule_run', now()->toIso8601String());
        } catch (\Throwable) {
        }
    }

    public static function lastScheduleRun(): ?string
    {
        $v = SiteSetting::get('cron_last_schedule_run', '');

        return $v !== '' && $v !== null ? (string) $v : null;
    }

    /** Absolute path to this install (works on any host). */
    public static function installPath(): string
    {
        return rtrim(str_replace('\\', '/', base_path()), '/');
    }

    /** Absolute path to artisan. */
    public static function artisanPath(): string
    {
        return self::installPath().'/artisan';
    }

    /**
     * Best-effort PHP CLI binary for this server.
     * Prefers PHP_BINARY when it looks like a CLI binary.
     */
    public static function phpBinary(): string
    {
        $candidates = [];

        if (defined('PHP_BINARY') && PHP_BINARY) {
            $candidates[] = PHP_BINARY;
        }

        $candidates = array_merge($candidates, [
            '/usr/bin/php',
            '/usr/local/bin/php',
            '/opt/alt/php83/usr/bin/php',
            '/opt/alt/php82/usr/bin/php',
            '/opt/alt/php81/usr/bin/php',
            'php',
        ]);

        foreach ($candidates as $bin) {
            if ($bin === 'php') {
                return 'php';
            }
            // PHP_BINARY is often the FPM/CGI binary; still usable for artisan on many hosts
            if (is_string($bin) && $bin !== '' && (is_executable($bin) || @is_file($bin))) {
                return $bin;
            }
        }

        return 'php';
    }

    /**
     * Recommended cron command for THIS installation (auto paths).
     * Safe default for cPanel, Hostinger, Plesk, VPS, etc.
     */
    public static function recommendedCommand(): string
    {
        $php = self::phpBinary();
        $artisan = self::artisanPath();

        return $php.' '.$artisan.' schedule:run >> /dev/null 2>&1';
    }

    /** Alternate: cd into app then relative artisan (some panels prefer this). */
    public static function alternateCommand(): string
    {
        $php = self::phpBinary();
        $base = self::installPath();

        return 'cd '.$base.' && '.$php.' artisan schedule:run >> /dev/null 2>&1';
    }

    /** @deprecated use recommendedCommand() */
    public static function hostingerCommand(): string
    {
        return self::recommendedCommand();
    }

    /**
     * @return list<array{label: string, command: string, note: string}>
     */
    public static function commandVariants(): array
    {
        return [
            [
                'label' => 'Recommended (works on most hosts)',
                'command' => self::recommendedCommand(),
                'note' => 'Uses the detected PHP binary and absolute path to artisan for this install.',
            ],
            [
                'label' => 'Alternate (cd into app folder)',
                'command' => self::alternateCommand(),
                'note' => 'Use if your panel requires a shell context in the project directory.',
            ],
            [
                'label' => 'Generic (if PHP path is wrong)',
                'command' => 'php '.self::artisanPath().' schedule:run >> /dev/null 2>&1',
                'note' => 'Replace php with the path your host shows under “Select PHP version” or “PHP CLI”.',
            ],
        ];
    }

    /** Detected environment summary for the admin UI. */
    public static function environmentInfo(): array
    {
        return [
            'app_url' => rtrim((string) config('app.url'), '/'),
            'install_path' => self::installPath(),
            'php_binary' => self::phpBinary(),
            'php_version' => PHP_VERSION,
            'timezone' => config('app.timezone', 'UTC'),
        ];
    }

    /** @return list<array{id: string, label: string, when: string, enabled: bool, command: string}> */
    public static function jobsOverview(): array
    {
        $days = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        return [
            [
                'id' => 'metrics',
                'label' => 'GitHub metrics sync',
                'when' => 'Daily at '.self::get('cron_metrics_time', '03:15').' (batch of '.self::get('cron_metrics_limit', 25).')',
                'enabled' => (bool) self::get('cron_metrics_enabled', true),
                'command' => 'alternova:sync-metrics --limit='.self::get('cron_metrics_limit', 25),
            ],
            [
                'id' => 'links',
                'label' => 'Link health check',
                'when' => ($days[(int) self::get('cron_links_day', 1)] ?? 'Monday').' at '.self::get('cron_links_time', '04:00').' (batch of '.self::get('cron_links_limit', 40).')',
                'enabled' => (bool) self::get('cron_links_enabled', true),
                'command' => 'alternova:check-links --limit='.self::get('cron_links_limit', 40),
            ],
            [
                'id' => 'digest',
                'label' => 'Weekly newsletter digest',
                'when' => ($days[(int) self::get('cron_digest_day', 1)] ?? 'Monday').' at '.self::get('cron_digest_time', '09:00'),
                'enabled' => (bool) self::get('cron_digest_enabled', true),
                'command' => 'alternova:send-digest --days=7',
            ],
            [
                'id' => 'notif_digest',
                'label' => 'Member notification digest',
                'when' => ($days[(int) self::get('cron_notif_digest_day', 1)] ?? 'Monday').' at '.self::get('cron_notif_digest_time', '09:30'),
                'enabled' => (bool) self::get('cron_notif_digest_enabled', true),
                'command' => 'alternova:send-notification-digest --days=7',
            ],
            [
                'id' => 'expire',
                'label' => 'Expire sponsored placements',
                'when' => 'Daily at '.self::get('cron_expire_sponsored_time', '00:30'),
                'enabled' => (bool) self::get('cron_expire_sponsored_enabled', true),
                'command' => 'alternova:expire-sponsored',
            ],
        ];
    }
}
