<?php

namespace Tests\Feature;

use App\Mail\NotificationDigestMail;
use App\Mail\WeeklyDigestMail;
use App\Models\NewsletterSubscriber;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ScheduledCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_metrics_sync_skips_unpublished_alternatives(): void
    {
        $tool = ProprietaryTool::factory()->create(['description' => null]);

        OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'description' => null,
            'is_published' => false,
        ]);

        Http::preventStrayRequests();

        $this->artisan('alternova:sync-metrics')
            ->expectsOutput('Syncing 0 alternative(s)…')
            ->expectsOutput('Done. OK: 0, failed: 0')
            ->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_notification_digest_respects_the_admin_disabled_setting(): void
    {
        $user = User::query()->create([
            'name' => 'Digest Member',
            'email' => 'digest-member@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'comment_reply',
            'title' => 'A new reply',
            'body' => 'Someone replied to your comment.',
            'url' => '/notifications',
        ]);

        SiteSetting::set('mail_notification_digest_enabled', false);
        Mail::fake();

        $this->artisan('alternova:send-notification-digest')
            ->expectsOutput('Notification digest disabled in settings (mail_notification_digest_enabled).')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

    public function test_notification_digest_sends_unread_notifications_to_their_owner(): void
    {
        $user = User::query()->create([
            'name' => 'Digest Member',
            'email' => 'digest-member@example.test',
            'password' => 'test-password',
            'role' => User::ROLE_MEMBER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'comment_reply',
            'title' => 'A new reply',
            'body' => 'Someone replied to your comment.',
            'url' => '/notifications',
        ]);

        Mail::fake();

        $this->artisan('alternova:send-notification-digest')
            ->expectsOutput('Users with unread notifications: 1')
            ->expectsOutput('Sent 1, failed 0.')
            ->assertExitCode(0);

        Mail::assertSent(NotificationDigestMail::class, function (NotificationDigestMail $mail) use ($user): bool {
            return $mail->hasTo($user->email)
                && $mail->notifications->count() === 1
                && $mail->unreadTotal === 1;
        });
    }
    public function test_weekly_digest_sends_to_active_subscribers_and_logs_the_delivery(): void
    {
        $tool = ProprietaryTool::factory()->create(['description' => null]);

        OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'description' => null,
            'is_published' => true,
            'is_featured' => false,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'weekly@example.test',
            'status' => 'active',
            'source' => 'footer',
            'unsubscribe_token' => str_repeat('d', 48),
            'subscribed_at' => now()->subMonth(),
        ]);

        SiteSetting::set('mail_digest_enabled', true);
        Mail::fake();

        $this->artisan('alternova:send-digest', ['--days' => 7])
            ->expectsOutput('Sent 1, failed 0.')
            ->assertExitCode(0);

        Mail::assertSent(WeeklyDigestMail::class, fn (WeeklyDigestMail $mail): bool => $mail->hasTo($subscriber->email));

        $this->assertDatabaseHas('newsletter_digest_logs', [
            'subscriber_count' => 1,
            'sent_count' => 1,
            'failed_count' => 0,
            'dry_run' => false,
        ]);
        $this->assertNotNull($subscriber->fresh()->last_digest_at);
    }

    public function test_weekly_digest_respects_the_admin_disabled_setting(): void
    {
        SiteSetting::set('mail_digest_enabled', false);
        Mail::fake();

        $this->artisan('alternova:send-digest')
            ->expectsOutput('Weekly digest is disabled in Email settings.')
            ->assertExitCode(0);

        Mail::assertNothingOutgoing();
    }

}
