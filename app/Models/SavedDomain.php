<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedDomain extends Model
{
    protected $fillable = [
        'session_id',
        'domain',
        'brandability',
        'status',
    ];

    protected $casts = [
        'brandability' => 'integer',
    ];
}
