<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueReport extends Model
{
    protected $fillable = [
        'open_source_alternative_id',
        'type',
        'email',
        'page_url',
        'message',
        'status',
        'ip_address',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
