<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
