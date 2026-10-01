<?php

use App\Support\CronSettings;
use Illuminate\Support\Facades\Schedule;

/*
| Hostinger: run this every minute (or every 5–15 minutes):
|   cd /home/USER/domains/YOURDOMAIN/public_html && php artisan schedule:run >> /dev/null 2>&1
|
| Toggles and times are controlled in Admin → Cron settings.
*/

Schedule::call(function () {
    CronSettings::markScheduleRan();
})->everyMinute()->name('alternova-cron-heartbeat');

// GitHub metrics
if (CronSettings::get('cron_metrics_enabled', true)) {
    $limit = max(1, min(200, (int) CronSettings::get('cron_metrics_limit', 25)));
    $time = (string) CronSettings::get('cron_metrics_time', '03:15');
    Schedule::command('alternova:sync-metrics --limit='.$limit)
        ->dailyAt($time)
        ->name('alternova-sync-metrics')
        ->withoutOverlapping();
}

// Broken link checks
if (CronSettings::get('cron_links_enabled', true)) {
    $limit = max(1, min(200, (int) CronSettings::get('cron_links_limit', 40)));
    $day = (int) CronSettings::get('cron_links_day', 1);
    $time = (string) CronSettings::get('cron_links_time', '04:00');
    Schedule::command('alternova:check-links --limit='.$limit)
        ->weeklyOn($day, $time)
        ->name('alternova-check-links')
        ->withoutOverlapping();
}

// Newsletter digest
if (CronSettings::get('cron_digest_enabled', true)) {
    $day = (int) CronSettings::get('cron_digest_day', 1);
    $time = (string) CronSettings::get('cron_digest_time', '09:00');
    Schedule::command('alternova:send-digest --days=7')
        ->weeklyOn($day, $time)
        ->name('alternova-send-digest')
        ->withoutOverlapping();
}

// In-app notification email digest
if (CronSettings::get('cron_notif_digest_enabled', true)) {
    $day = (int) CronSettings::get('cron_notif_digest_day', 1);
    $time = (string) CronSettings::get('cron_notif_digest_time', '09:30');
    Schedule::command('alternova:send-notification-digest --days=7')
        ->weeklyOn($day, $time)
        ->name('alternova-send-notification-digest')
        ->withoutOverlapping();
}

// Sponsored expiry
if (CronSettings::get('cron_expire_sponsored_enabled', true)) {
    $time = (string) CronSettings::get('cron_expire_sponsored_time', '00:30');
    Schedule::command('alternova:expire-sponsored')
        ->dailyAt($time)
        ->name('alternova-expire-sponsored')
        ->withoutOverlapping();
}
