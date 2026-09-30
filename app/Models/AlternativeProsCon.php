<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlternativeProsCon extends Model
{
    protected $table = 'alternative_pros_cons';

    protected $fillable = [
        'open_source_alternative_id',
        'type',
        'body',
        'votes_count',
        'is_approved',
        'is_featured',
        'author_name',
        'author_email',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'votes_count' => 'integer',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ProsConsVote::class, 'alternative_pros_con_id');
    }

    public function isPro(): bool
    {
        return $this->type === 'pro';
    }
}
