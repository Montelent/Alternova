<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\NewsletterDigestLog;
use App\Models\NewsletterSubscriber;
use App\Models\OpenSourceAlternative;
use App\Models\SiteSetting;
use App\Support\MailSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendWeeklyDigestCommand extends Command
{
    protected $signature = 'alternova:send-digest
                            {--days=7 : Lookback window in days}
                            {--dry-run : Do not send mail}
                            {--force : Send even when there is no new content (featured fallback)}
                            {--ignore-toggle : Ignore the admin digest enabled setting}';

    protected $description = 'Email active newsletter subscribers a weekly alternatives digest';

    public function handle(): int
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            $this->error('newsletter_subscribers table missing. Run migrations.');

            return self::FAILURE;
        }

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        $enabled = true;
        try {
            $enabled = SiteSetting::getBool('mail_digest_enabled', true);
        } catch (\Throwable) {
        }

        if (! $enabled && ! $this->option('ignore-toggle')) {
            $this->warn('Weekly digest is disabled in Email settings.');

            return self::SUCCESS;
        }

        $days = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $new = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $updated = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('updated_at', '>=', $since)
            ->where('created_at', '<', $since)
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        $featured = collect();
        if ($new->isEmpty() && $updated->isEmpty() && ($this->option('force') || SiteSetting::getBool('mail_digest_include_featured', true))) {
            $featured = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->where('is_featured', true)
                ->orderByDesc('overall_health_score')
                ->limit(5)
                ->get();
        }

        $subscribers = NewsletterSubscriber::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $this->info("New: {$new->count()} · Updated: {$updated->count()} · Featured fallback: {$featured->count()} · Subscribers: {$subscribers->count()}");

        if ($this->option('dry-run')) {
            $this->logRun($days, $new->count(), $updated->count(), $subscribers->count(), 0, 0, true, 'Dry run');
            $this->warn('Dry run — no emails sent.');

            return self::SUCCESS;
        }

        if ($subscribers->isEmpty()) {
            $this->warn('No active subscribers.');
            $this->logRun($days, $new->count(), $updated->count(), 0, 0, 0, false, 'No subscribers');

            return self::SUCCESS;
        }

        if ($new->isEmpty() && $updated->isEmpty() && $featured->isEmpty()) {
            $this->warn('Nothing to report — skipping send. Use --force to include featured picks.');
            $this->logRun($days, 0, 0, $subscribers->count(), 0, 0, false, 'Empty content');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $sub) {
            try {
                $unsub = $sub->unsubscribeUrl();
                Mail::to($sub->email)->send(new WeeklyDigestMail($new, $updated, $unsub, $featured, $days));
                $sent++;
                try {
                    $sub->forceFill(['last_digest_at' => now()])->save();
                } catch (\Throwable) {
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->error($sub->email.': '.$e->getMessage());
            }
        }

        $this->info("Sent {$sent}, failed {$failed}.");
        $this->logRun($days, $new->count(), $updated->count(), $subscribers->count(), $sent, $failed, false, null);

        return self::SUCCESS;
    }

    protected function logRun(
        int $days,
        int $new,
        int $updated,
        int $subs,
        int $sent,
        int $failed,
        bool $dry,
        ?string $notes
    ): void {
        try {
            if (! Schema::hasTable('newsletter_digest_logs')) {
                return;
            }
            NewsletterDigestLog::create([
                'days' => $days,
                'new_count' => $new,
                'updated_count' => $updated,
                'subscriber_count' => $subs,
                'sent_count' => $sent,
                'failed_count' => $failed,
                'dry_run' => $dry,
                'notes' => $notes,
            ]);
        } catch (\Throwable) {
        }
    }
}
