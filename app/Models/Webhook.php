<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    public const EVENT_ALTERNATIVE_PUBLISHED = 'alternative.published';

    public const EVENT_HEALTH_DROP = 'health.drop';

    public const EVENT_COLLECTION_PUBLISHED = 'collection.published';

    public static function eventOptions(): array
    {
        return [
            self::EVENT_ALTERNATIVE_PUBLISHED => 'Alternative published',
            self::EVENT_HEALTH_DROP => 'Health score drop',
            self::EVENT_COLLECTION_PUBLISHED => 'Collection published',
        ];
    }

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
        'success_count',
        'failure_count',
        'last_triggered_at',
        'last_error',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function listensFor(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }
}
