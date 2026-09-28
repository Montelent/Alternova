<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class ProprietaryTool extends Model
{
    use HasFactory, SoftDeletes, HasTags, Searchable;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'website_url',
        'description',
        'key_features',
        'target_audience',
        'is_published',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'key_features' => 'array',
        'is_published' => 'boolean',
    ];

    public function openSourceAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class);
    }

    public function publishedAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class)
            ->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        return static::query()
            ->where($field, $value)
            ->where('is_published', true)
            ->first();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'target_audience' => $this->target_audience,
            'key_features' => $this->key_features,
        ];
    }
}
