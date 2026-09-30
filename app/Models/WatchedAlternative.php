<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchedAlternative extends Model
{
    protected $fillable = [
        'user_id',
        'open_source_alternative_id',
        'notify_health_drop',
        'last_notified_score',
        'last_notified_at',
    ];

    protected $casts = [
        'notify_health_drop' => 'boolean',
        'last_notified_score' => 'float',
        'last_notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
