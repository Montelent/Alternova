<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $fillable = [
        'provider',
        'domain',
        'destination_url',
        'ip_address',
        'user_agent',
        'referer',
    ];
}
