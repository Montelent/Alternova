<?php

namespace App\Support;

use App\Models\SiteSetting;

class CronSettings
{
    public static function defaults(): array
    {
        return [
            'cron_metrics_enabled' => true,
            'cron_metrics_time' => '03:15',
            'cron_metrics_limit' => 25,
            'cron_links_enabled' => true,
            'cron_links_day' => 1, // Monday
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

    /** Suggested Hostinger cron expression (once per minute is ideal for Laravel). */
    public static function hostingerCommand(): string
    {
        $base = base_path();

        return 'cd '.$base.' && php artisan schedule:run >> /dev/null 2>&1';
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
