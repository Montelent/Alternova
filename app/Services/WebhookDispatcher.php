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

    protected function deliver(Webhook $hook, string $event, array $body): void
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
            $response = Http::timeout(8)
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

            if (Schema::hasTable('webhook_deliveries')) {
                WebhookDelivery::create([
                    'webhook_id' => $hook->id,
                    'event' => $event,
                    'status_code' => $status,
                    'success' => $success,
                    'response_body' => $responseBody,
                    'error' => $error,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Webhook delivery log failed: '.$e->getMessage());
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
