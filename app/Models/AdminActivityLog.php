<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        ?Model $subject = null,
        array $properties = [],
        ?string $label = null
    ): void {
        try {
            static::query()->create([
                'user_id' => auth()->id(),
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'subject_label' => $label
                    ?? ($subject->name ?? $subject->slug ?? null),
                'properties' => $properties ?: null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            // table may not exist yet
        }
    }
}
