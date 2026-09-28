<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeVote extends Model
{
    protected $fillable = [
        'open_source_alternative_id',
        'voter_key',
        'ip_address',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
