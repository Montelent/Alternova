<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CollectionSubmission extends Model
{
    protected $fillable = [
        'submitter_name',
        'submitter_email',
        'title',
        'proposed_slug',
        'description',
        'intro',
        'alternative_slugs',
        'notes',
        'status',
        'tracking_token',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'created_collection_id',
        'ip_address',
    ];

    protected $casts = [
        'alternative_slugs' => 'array',
        'reviewed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (CollectionSubmission $submission) {
            if (empty($submission->tracking_token)) {
                $submission->tracking_token = Str::random(40);
            }
        });
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function createdCollection(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'created_collection_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst((string) $this->status),
        };
    }
}
