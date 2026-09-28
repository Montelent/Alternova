<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteAlternative extends Model
{
    protected $fillable = [
        'session_id',
        'open_source_alternative_id',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
