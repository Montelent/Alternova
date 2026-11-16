<?php

namespace Tests\Feature;

use App\Livewire\NewsletterSubscribe;
use App\Models\NewsletterSubscriber;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Installer::lock();
    }

    protected function tearDown(): void
    {
        File::delete(Installer::lockPath());
        File::delete(storage_path('app/'.Installer::REOPEN_FILE));
        parent::tearDown();
    }

    public function test_newsletter_subscription_normalizes_email_and_creates_a_secure_unsubscribe_token(): void
    {
        Livewire::test(NewsletterSubscribe::class)
            ->set('email', '  USER@Example.Test ')
            ->set('source', 'footer')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('success', true)
            ->assertSet('email', '');

        $subscriber = NewsletterSubscriber::query()->where('email', 'user@example.test')->firstOrFail();

        $this->assertSame('active', $subscriber->status);
        $this->assertSame('footer', $subscriber->source);
        $this->assertNotNull($subscriber->subscribed_at);
        $this->assertSame(48, strlen((string) $subscriber->unsubscribe_token));
    }

    public function test_duplicate_active_subscription_is_idempotent(): void
    {
        $existing = NewsletterSubscriber::query()->create([
            'email' => 'already@example.test',
            'status' => 'active',
            'unsubscribe_token' => str_repeat('a', 48),
            'subscribed_at' => now(),
        ]);

        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'ALREADY@example.test')
            ->call('subscribe')
            ->assertSet('success', true)
            ->assertSee('already subscribed');

        $this->assertSame(1, NewsletterSubscriber::query()->where('email', $existing->email)->count());
    }

    public function test_get_unsubscribe_link_does_not_change_subscription_until_confirmation_post(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'unsubscribe@example.test',
            'status' => 'active',
            'unsubscribe_token' => str_repeat('b', 48),
            'subscribed_at' => now(),
        ]);
        $oldToken = $subscriber->unsubscribe_token;

        $this->get(route('newsletter.unsubscribe', ['token' => $oldToken]))
            ->assertOk()
            ->assertSee('Confirm that you want to unsubscribe');

        $this->assertSame('active', $subscriber->fresh()->status);

        $this->post(route('newsletter.unsubscribe.confirm'), ['token' => $oldToken])
            ->assertOk()
            ->assertSee('You have been unsubscribed');

        $subscriber->refresh();
        $this->assertSame('unsubscribed', $subscriber->status);
        $this->assertNotSame($oldToken, $subscriber->unsubscribe_token);

        $this->get(route('newsletter.unsubscribe', ['token' => $oldToken]))
            ->assertOk()
            ->assertSee('Invalid or missing unsubscribe link');
    }

    public function test_resubscription_keeps_the_rotated_token_and_does_not_restore_an_old_unsubscribe_link(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'returning@example.test',
            'status' => 'unsubscribed',
            'unsubscribe_token' => str_repeat('c', 48),
            'subscribed_at' => now()->subDay(),
            'unsubscribed_at' => now(),
        ]);
        $revokedToken = $subscriber->unsubscribe_token;

        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'returning@example.test')
            ->call('subscribe')
            ->assertSet('success', true);

        $subscriber->refresh();
        $this->assertSame('active', $subscriber->status);
        $this->assertSame($revokedToken, $subscriber->unsubscribe_token);
        $this->assertSame(1, NewsletterSubscriber::query()->where('email', $subscriber->email)->count());
    }

    public function test_newsletter_subscription_is_rate_limited_per_ip(): void
    {
        RateLimiter::clear('newsletter:127.0.0.1');

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            Livewire::test(NewsletterSubscribe::class)
                ->set('email', "subscriber{$attempt}@example.test")
                ->call('subscribe')
                ->assertSet('success', true);
        }

        Livewire::test(NewsletterSubscribe::class)
            ->set('email', 'sixth@example.test')
            ->call('subscribe')
            ->assertSet('success', false)
            ->assertSee('Too many attempts');

        $this->assertDatabaseCount('newsletter_subscribers', 5);
    }
}
