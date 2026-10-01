<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
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
        'focus_keyword',
        'robots_meta',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_url',
    ];

    protected $casts = [
        'key_features' => 'array',
        'is_published' => 'boolean',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->logo_path, 'uploads')
            ?? MediaUrl::make($this->logo_path, 'public');
    }

    public function openSourceAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class);
    }

    public function publishedAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class)
            ->where('is_published', true);
    }

    public function linkedAlternatives()
    {
        $primary = $this->publishedAlternatives()
            ->with(['repoMetric', 'tags'])
            ->orderByDesc('overall_health_score')
            ->get();

        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $viaPivot = OpenSourceAlternative::query()
                    ->where('is_published', true)
                    ->whereHas('proprietaryTools', fn ($q) => $q->where('proprietary_tools.id', $this->id))
                    ->with(['repoMetric', 'tags'])
                    ->orderByDesc('overall_health_score')
                    ->get();

                return $primary->concat($viaPivot)->unique('id')->sortByDesc('overall_health_score')->values();
            }
        } catch (\Throwable) {
        }

        return $primary;
    }

    public function alternativesMany(): BelongsToMany
    {
        return $this->belongsToMany(
            OpenSourceAlternative::class,
            'alternative_proprietary_tool',
            'proprietary_tool_id',
            'open_source_alternative_id'
        )->withPivot('position')->withTimestamps();
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

    public function publicUrl(): string
    {
        return route('alternativesto.show', $this->slug);
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
