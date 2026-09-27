<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainSearchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'seed_keywords',
        'selected_tlds',
        'domain_generated_count',
        'available_count',
        'ip_address',
        'user_id',
    ];

    protected $casts = [
        'seed_keywords' => 'array',
        'selected_tlds' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
