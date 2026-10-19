<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class AdminDraft extends Model
{
    protected $fillable = [
        'user_id',
        'resource',
        'record_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'record_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Unfinished "create" form draft for the current user + resource (record_id = 0).
     */
    public static function unfinishedCreateFor(string $resource, ?int $userId = null): ?self
    {
        if (! Schema::hasTable('admin_drafts')) {
            return null;
        }

        $userId = $userId ?? auth()->id();
        if (! $userId) {
            return null;
        }

        return static::query()
            ->where('user_id', $userId)
            ->where('resource', $resource)
            ->where('record_id', 0)
            ->latest('updated_at')
            ->first();
    }

    public function summaryLabel(): string
    {
        $payload = $this->payload ?? [];
        $name = $payload['name'] ?? $payload['title'] ?? null;
        if (is_string($name) && trim($name) !== '') {
            return trim($name);
        }

        return 'Unfinished form';
    }
}
