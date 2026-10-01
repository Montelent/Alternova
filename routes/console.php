<?php

use App\Support\CronSettings;
use Illuminate\Support\Facades\Schedule;

/*
| Cron is host-agnostic. In Admin → Cron settings the panel shows the
| recommended command for THIS install (auto PHP path + artisan path).
| Run schedule:run every minute from the customer’s hosting panel.
*/

Schedule::call(function () {
    CronSettings::markScheduleRan();
})->everyMinute()->name('alternova-cron-heartbeat');

if (CronSettings::get('cron_metrics_enabled', true)) {
    $limit = max(1, min(200, (int) CronSettings::get('cron_metrics_limit', 25)));
    $time = (string) CronSettings::get('cron_metrics_time', '03:15');
    Schedule::command('alternova:sync-metrics --limit='.$limit)
        ->dailyAt($time)
        ->name('alternova-sync-metrics')
        ->withoutOverlapping();
}

if (CronSettings::get('cron_links_enabled', true)) {
    $limit = max(1, min(200, (int) CronSettings::get('cron_links_limit', 40)));
    $day = (int) CronSettings::get('cron_links_day', 1);
    $time = (string) CronSettings::get('cron_links_time', '04:00');
    Schedule::command('alternova:check-links --limit='.$limit)
        ->weeklyOn($day, $time)
        ->name('alternova-check-links')
        ->withoutOverlapping();
}

if (CronSettings::get('cron_digest_enabled', true)) {
    $day = (int) CronSettings::get('cron_digest_day', 1);
    $time = (string) CronSettings::get('cron_digest_time', '09:00');
    Schedule::command('alternova:send-digest --days=7')
        ->weeklyOn($day, $time)
        ->name('alternova-send-digest')
        ->withoutOverlapping();
}

if (CronSettings::get('cron_notif_digest_enabled', true)) {
    $day = (int) CronSettings::get('cron_notif_digest_day', 1);
    $time = (string) CronSettings::get('cron_notif_digest_time', '09:30');
    Schedule::command('alternova:send-notification-digest --days=7')
        ->weeklyOn($day, $time)
        ->name('alternova-send-notification-digest')
        ->withoutOverlapping();
}

if (CronSettings::get('cron_expire_sponsored_enabled', true)) {
    $time = (string) CronSettings::get('cron_expire_sponsored_time', '00:30');
    Schedule::command('alternova:expire-sponsored')
        ->dailyAt($time)
        ->name('alternova-expire-sponsored')
        ->withoutOverlapping();
}
