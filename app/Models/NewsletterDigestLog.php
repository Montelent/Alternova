<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterDigestLog extends Model
{
    protected $fillable = [
        'days',
        'new_count',
        'updated_count',
        'subscriber_count',
        'sent_count',
        'failed_count',
        'dry_run',
        'notes',
    ];

    protected $casts = [
        'dry_run' => 'boolean',
    ];
}
