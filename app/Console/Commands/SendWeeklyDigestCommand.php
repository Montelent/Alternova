<?php

namespace App\Console\Commands;

use App\Mail\WeeklyDigestMail;
use App\Models\NewsletterSubscriber;
use App\Models\OpenSourceAlternative;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendWeeklyDigestCommand extends Command
{
    protected $signature = 'alternova:send-digest {--days=7 : Lookback window in days} {--dry-run : Do not send mail}';

    protected $description = 'Email active newsletter subscribers a weekly alternatives digest';

    public function handle(): int
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            $this->error('newsletter_subscribers table missing. Run migrations.');

            return self::FAILURE;
        }

        $days = max(1, (int) $this->option('days'));
        $since = now()->subDays($days);

        $new = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->where('is_published', true)
            ->where('created_at', '>=', $since)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $updated = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->where('is_published', true)
            ->where('updated_at', '>=', $since)
            ->where('created_at', '<', $since)
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get();

        $subscribers = NewsletterSubscriber::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $this->info("New: {$new->count()} · Updated: {$updated->count()} · Subscribers: {$subscribers->count()}");

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no emails sent.');

            return self::SUCCESS;
        }

        if ($subscribers->isEmpty()) {
            $this->warn('No active subscribers.');

            return self::SUCCESS;
        }

        // Skip empty digests unless forced by having any content
        if ($new->isEmpty() && $updated->isEmpty()) {
            $this->warn('Nothing new to report — skipping send.');

            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $sub) {
            try {
                $unsub = route('newsletter.unsubscribe', ['email' => $sub->email]);
                Mail::to($sub->email)->send(new WeeklyDigestMail($new, $updated, $unsub));
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error($sub->email.': '.$e->getMessage());
            }
        }

        $this->info("Sent {$sent}, failed {$failed}.");

        return self::SUCCESS;
    }
}
