<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlternativeComment extends Model
{
    protected $fillable = [
        'open_source_alternative_id',
        'user_id',
        'parent_id',
        'author_name',
        'author_email',
        'body',
        'is_approved',
        'is_hidden',
        'ip_address',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_hidden' => 'boolean',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_approved', true)->where('is_hidden', false);
    }

    public function approve(): void
    {
        $this->forceFill(['is_approved' => true, 'is_hidden' => false])->save();
    }

    public function hide(): void
    {
        $this->forceFill(['is_hidden' => true])->save();
    }
}
