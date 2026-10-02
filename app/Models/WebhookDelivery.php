<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'status_code',
        'success',
        'response_body',
        'error',
    ];

    protected $casts = [
        'success' => 'boolean',
        'status_code' => 'integer',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /** @return array<string, mixed>|null */
    public function decodedPayload(): ?array
    {
        if (! $this->payload) {
            return null;
        }

        $decoded = json_decode($this->payload, true);

        return is_array($decoded) ? $decoded : null;
    }
}
