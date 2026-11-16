<?php

namespace Tests\Feature;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\WebhookDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookDispatcherIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_delivery_signs_payload_and_records_success(): void
    {
        $hook = Webhook::query()->create([
            'name' => 'Deployment events',
            'url' => 'https://1.1.1.1/webhook',
            'secret' => 'webhook-signing-secret',
            'events' => [Webhook::EVENT_ALTERNATIVE_PUBLISHED],
            'is_active' => true,
        ]);

        $body = [
            'event' => Webhook::EVENT_ALTERNATIVE_PUBLISHED,
            'timestamp' => now()->toIso8601String(),
            'data' => ['id' => 42, 'slug' => 'sample-project'],
        ];
        $json = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = 'sha256='.hash_hmac('sha256', $json, 'webhook-signing-secret');

        Http::preventStrayRequests();
        Http::fake([
            'https://1.1.1.1/webhook' => Http::response('accepted', 202),
        ]);

        $delivery = app(WebhookDispatcher::class)->deliver(
            $hook,
            Webhook::EVENT_ALTERNATIVE_PUBLISHED,
            $body
        );

        $this->assertTrue($delivery->success);
        $this->assertSame(202, $delivery->status_code);
        $this->assertSame($json, $delivery->payload);
        $this->assertNull($delivery->error);

        $hook->refresh();
        $this->assertSame(1, $hook->success_count);
        $this->assertSame(0, $hook->failure_count);
        $this->assertNotNull($hook->last_triggered_at);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://1.1.1.1/webhook'
            && $request->body() === $json
            && $request->hasHeader('X-Alternova-Event', Webhook::EVENT_ALTERNATIVE_PUBLISHED)
            && $request->hasHeader('X-Alternova-Signature', $signature));
    }

    public function test_private_webhook_destinations_are_rejected_without_network_requests(): void
    {
        $hook = Webhook::query()->create([
            'name' => 'Unsafe destination',
            'url' => 'http://127.0.0.1/internal-hook',
            'secret' => 'unused-secret',
            'events' => [Webhook::EVENT_HEALTH_DROP],
            'is_active' => true,
        ]);

        Http::preventStrayRequests();

        $delivery = app(WebhookDispatcher::class)->deliver(
            $hook,
            Webhook::EVENT_HEALTH_DROP,
            ['score' => 10]
        );

        $this->assertFalse($delivery->success);
        $this->assertNull($delivery->status_code);
        $this->assertStringContainsString('public HTTP or HTTPS', (string) $delivery->error);
        $this->assertDatabaseHas('webhooks', [
            'id' => $hook->id,
            'success_count' => 0,
            'failure_count' => 1,
        ]);

        Http::assertNothingSent();
    }

    public function test_retry_reuses_the_original_delivery_record_and_payload(): void
    {
        $hook = Webhook::query()->create([
            'name' => 'Retry endpoint',
            'url' => 'https://1.1.1.1/retry',
            'secret' => 'retry-secret',
            'events' => [Webhook::EVENT_COLLECTION_PUBLISHED],
            'is_active' => true,
        ]);

        $body = [
            'event' => Webhook::EVENT_COLLECTION_PUBLISHED,
            'timestamp' => now()->toIso8601String(),
            'data' => ['id' => 8, 'slug' => 'developer-tools'],
        ];

        Http::preventStrayRequests();
        Http::fake([
            'https://1.1.1.1/retry' => Http::sequence()
                ->push('temporary failure', 503)
                ->push('accepted', 200),
        ]);

        $dispatcher = app(WebhookDispatcher::class);
        $first = $dispatcher->deliver($hook, Webhook::EVENT_COLLECTION_PUBLISHED, $body);
        $this->assertFalse($first->success);
        $this->assertSame(503, $first->status_code);

        $retried = $dispatcher->retry($first);

        $this->assertSame($first->id, $retried->id);
        $this->assertTrue($retried->success);
        $this->assertSame(200, $retried->status_code);
        $this->assertSame($first->payload, $retried->payload);
        $this->assertSame(1, WebhookDelivery::query()->count());

        $hook->refresh();
        $this->assertSame(1, $hook->success_count);
        $this->assertSame(1, $hook->failure_count);
    }
}
