<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlternativeSubmission extends Model
{
    protected $fillable = [
        'submitter_name',
        'submitter_email',
        'proprietary_name',
        'alternative_name',
        'repo_url',
        'website_url',
        'description',
        'license_type',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'created_alternative_id',
        'ip_address',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
