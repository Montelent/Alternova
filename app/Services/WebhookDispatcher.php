<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebhookDispatcher
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('webhooks');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $event, array $payload): void
    {
        if (! $this->ready()) {
            return;
        }

        // Deliver after the HTTP response so admin publish/save is not blocked by outbound POSTs
        dispatch(function () use ($event, $payload) {
            $this->dispatchNow($event, $payload);
        })->afterResponse();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchNow(string $event, array $payload): void
    {
        if (! $this->ready()) {
            return;
        }

        try {
            $hooks = Webhook::query()
                ->where('is_active', true)
                ->get()
                ->filter(fn (Webhook $w) => $w->listensFor($event));
        } catch (\Throwable $e) {
            Log::warning('Webhook query failed: '.$e->getMessage());

            return;
        }

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => $payload,
        ];

        foreach ($hooks as $hook) {
            $this->deliver($hook, $event, $body);
        }
    }

    /**
     * Re-send a stored delivery body to the same webhook URL.
     */
    public function retry(WebhookDelivery $delivery): WebhookDelivery
    {
        $hook = $delivery->webhook;
        if (! $hook) {
            throw new \RuntimeException('Webhook no longer exists for this delivery.');
        }

        $body = $delivery->decodedPayload();
        if ($body === null) {
            $body = [
                'event' => $delivery->event,
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'retry' => true,
                    'original_delivery_id' => $delivery->id,
                    'message' => 'Retry without original payload (delivery was logged before payload storage).',
                ],
            ];
        }

        return $this->deliver($hook, (string) $delivery->event, $body, $delivery);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    public function deliver(Webhook $hook, string $event, array $body, ?WebhookDelivery $existing = null): WebhookDelivery
    {
        $json = json_encode($body, JSON_UNESCAPED_SLASHES);
        $signature = null;
        if ($hook->secret) {
            $signature = hash_hmac('sha256', $json ?: '', $hook->secret);
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Alternova-Webhooks/1.0',
            'X-Alternova-Event' => $event,
            'X-Alternova-Delivery' => (string) Str::uuid(),
        ];
        if ($signature) {
            $headers['X-Alternova-Signature'] = 'sha256='.$signature;
        }

        $success = false;
        $status = null;
        $responseBody = null;
        $error = null;

        try {
            $response = Http::timeout(6)
                ->connectTimeout(3)
                ->withHeaders($headers)
                ->withBody($json ?: '{}', 'application/json')
                ->post($hook->url);

            $status = $response->status();
            $responseBody = Str::limit($response->body(), 2000, '');
            $success = $response->successful();

            if (! $success) {
                $error = 'HTTP '.$status;
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        try {
            $hook->forceFill([
                'last_triggered_at' => now(),
                'last_error' => $success ? null : $error,
                'success_count' => $hook->success_count + ($success ? 1 : 0),
                'failure_count' => $hook->failure_count + ($success ? 0 : 1),
            ])->save();
        } catch (\Throwable $e) {
            Log::warning('Webhook counter update failed: '.$e->getMessage());
        }

        $attrs = [
            'webhook_id' => $hook->id,
            'event' => $event,
            'status_code' => $status,
            'success' => $success,
            'response_body' => $responseBody,
            'error' => $error,
        ];

        if (Schema::hasColumn('webhook_deliveries', 'payload')) {
            $attrs['payload'] = $json;
        }

        try {
            if ($existing) {
                $existing->forceFill($attrs)->save();

                return $existing->fresh();
            }

            return WebhookDelivery::query()->create($attrs);
        } catch (\Throwable $e) {
            Log::warning('Webhook delivery log failed: '.$e->getMessage());

            $fallback = new WebhookDelivery($attrs);

            return $fallback;
        }
    }

    public function alternativePublishedPayload($alt): array
    {
        return [
            'id' => $alt->id,
            'name' => $alt->name,
            'slug' => $alt->slug,
            'url' => url('/alternatives/'.$alt->slug),
            'health_score' => (float) $alt->overall_health_score,
            'license' => $alt->license_type,
            'repo_url' => $alt->repo_url,
        ];
    }

    public function healthDropPayload($alt, float $previous, float $new): array
    {
        return [
            'id' => $alt->id,
            'name' => $alt->name,
            'slug' => $alt->slug,
            'url' => url('/alternatives/'.$alt->slug),
            'previous_score' => $previous,
            'new_score' => $new,
            'drop' => round($previous - $new, 2),
        ];
    }

    public function collectionPublishedPayload($collection): array
    {
        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'slug' => $collection->slug,
            'url' => url('/collections/'.$collection->slug),
            'items_count' => method_exists($collection, 'items')
                ? $collection->items()->count()
                : null,
        ];
    }
}
