<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProsConsVote extends Model
{
    protected $fillable = [
        'alternative_pros_con_id',
        'voter_key',
        'ip_address',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AlternativeProsCon::class, 'alternative_pros_con_id');
    }
}
