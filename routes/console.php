<?php

use Illuminate\Support\Facades\Schedule;

/*
| Hostinger cron example (every hour):
| cd /home/USER/domains/YOURDOMAIN/public_html && php artisan schedule:run >> /dev/null 2>&1
*/

Schedule::command('alternova:sync-metrics --limit=25')->dailyAt('03:15');
Schedule::command('alternova:check-links --limit=40')->weeklyOn(1, '04:00');
Schedule::command('alternova:send-digest --days=7')->weeklyOn(1, '09:00');
