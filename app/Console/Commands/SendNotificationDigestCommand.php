<?php

namespace App\Console\Commands;

use App\Mail\NotificationDigestMail;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\MailSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendNotificationDigestCommand extends Command
{
    protected $signature = 'alternova:send-notification-digest
                            {--days=7 : Only include notifications newer than this}
                            {--dry-run : List recipients without sending}
                            {--force : Send even if mail_notification_digest_enabled is off}';

    protected $description = 'Email members a digest of unread in-app notifications';

    public function handle(): int
    {
        if (! Schema::hasTable('user_notifications')) {
            $this->error('user_notifications table missing. Run migrations.');

            return self::FAILURE;
        }

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        $enabled = true;
        try {
            $enabled = SiteSetting::getBool('mail_notification_digest_enabled', true);
        } catch (\Throwable) {
        }

        if (! $enabled && ! $this->option('force')) {
            $this->warn('Notification digest disabled in settings (mail_notification_digest_enabled).');

            return self::SUCCESS;
        }

        $days = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $userIds = UserNotification::query()
            ->whereNull('read_at')
            ->where('created_at', '>=', $since)
            ->distinct()
            ->pluck('user_id');

        $this->info('Users with unread notifications: '.$userIds->count());

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no emails sent.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($userIds as $userId) {
            $user = User::query()->find($userId);
            if (! $user || ! $user->email) {
                continue;
            }

            $items = UserNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->where('created_at', '>=', $since)
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            if ($items->isEmpty()) {
                continue;
            }

            $totalUnread = UserNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            try {
                Mail::to($user->email)->send(new NotificationDigestMail(
                    userName: (string) $user->name,
                    notifications: $items,
                    unreadTotal: $totalUnread,
                ));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error($user->email.': '.$e->getMessage());
            }
        }

        $this->info("Sent {$sent}, failed {$failed}.");

        return self::SUCCESS;
    }
}
