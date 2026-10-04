<?php

namespace App\Models;

use App\Services\HealthHistoryService;
use App\Services\SeoManager;
use App\Services\WebhookDispatcher;
use App\Support\MediaUrl;
use App\Support\QueryCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class OpenSourceAlternative extends Model
{
    use HasFactory, SoftDeletes, HasTags, Searchable;

    protected $fillable = [
        'proprietary_tool_id',
        'name',
        'slug',
        'logo_path',
        'external_logo_url',
        'repo_url',
        'website_url',
        'description',
        'license_type',
        'self_host_difficulty',
        'docker_compose_blueprint',
        'primary_language',
        'overall_health_score',
        'votes_count',
        'is_published',
        'is_featured',
        'is_sponsored',
        'sponsored_until',
        'sponsor_label',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'robots_meta',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_url',
        'editor_note',
        'changelog',
        'gallery_urls',
        'gallery_paths',
        'pros',
        'cons',
        'repo_reachable',
        'website_reachable',
        'links_checked_at',
    ];

    protected $appends = ['logo_url'];

    protected $casts = [
        'self_host_difficulty' => 'integer',
        'overall_health_score' => 'float',
        'votes_count' => 'integer',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'sponsored_until' => 'datetime',
        'pros' => 'array',
        'cons' => 'array',
        'gallery_urls' => 'array',
        'gallery_paths' => 'array',
        'repo_reachable' => 'boolean',
        'website_reachable' => 'boolean',
        'links_checked_at' => 'datetime',
    ];

    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->attributes['logo_path'] ?? null;
        if (is_string($path) && trim($path) !== '') {
            return MediaUrl::make(trim($path), 'uploads');
        }

        $external = $this->attributes['external_logo_url'] ?? null;
        if (is_string($external) && trim($external) !== '') {
            return MediaUrl::make(trim($external), 'uploads');
        }

        return null;
    }

    protected static function booted(): void
    {
        $bustCaches = function () {
            try {
                QueryCache::bustCatalog();
            } catch (\Throwable) {
                try {
                    Cache::forget('home.page.v1');
                } catch (\Throwable) {
                }
            }
            try {
                Cache::forget('api.alt.show.v1.'.md5((string) (static::query()->value('slug') ?? '')));
            } catch (\Throwable) {
            }
        };

        static::saved(function (OpenSourceAlternative $alt) use ($bustCaches) {
            $bustCaches();
            try {
                Cache::forget('api.alt.show.v1.'.md5($alt->slug));
            } catch (\Throwable) {
            }
        });
        static::deleted(function (OpenSourceAlternative $alt) use ($bustCaches) {
            $bustCaches();
            try {
                Cache::forget('api.alt.show.v1.'.md5($alt->slug));
            } catch (\Throwable) {
            }
        });

        static::updated(function (OpenSourceAlternative $alt) {
            try {
                if ($alt->wasChanged('is_published') && $alt->is_published) {
                    $dispatcher = app(WebhookDispatcher::class);
                    $dispatcher->dispatch(
                        Webhook::EVENT_ALTERNATIVE_PUBLISHED,
                        $dispatcher->alternativePublishedPayload($alt)
                    );
                }
            } catch (\Throwable) {
            }
        });

        static::created(function (OpenSourceAlternative $alt) {
            try {
                if ($alt->is_published) {
                    $dispatcher = app(WebhookDispatcher::class);
                    $dispatcher->dispatch(
                        Webhook::EVENT_ALTERNATIVE_PUBLISHED,
                        $dispatcher->alternativePublishedPayload($alt)
                    );
                }
            } catch (\Throwable) {
            }
        });
    }

    public function proprietaryTool(): BelongsTo
    {
        return $this->belongsTo(ProprietaryTool::class);
    }

    public function proprietaryTools(): BelongsToMany
    {
        return $this->belongsToMany(
            ProprietaryTool::class,
            'alternative_proprietary_tool',
            'open_source_alternative_id',
            'proprietary_tool_id'
        )->withTimestamps();
    }

    public function repoMetric(): HasOne
    {
        return $this->hasOne(RepoMetric::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AlternativeVote::class);
    }

    public function healthSnapshots(): HasMany
    {
        return $this->hasMany(HealthScoreSnapshot::class);
    }

    public function watches(): HasMany
    {
        return $this->hasMany(WatchedAlternative::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AlternativeComment::class);
    }

    public function prosCons(): HasMany
    {
        return $this->hasMany(AlternativeProsCon::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @return list<string>
     */
    public function galleryImageUrls(): array
    {
        $urls = [];

        foreach ((array) ($this->gallery_paths ?? []) as $path) {
            if (! is_string($path) || trim($path) === '') {
                continue;
            }
            $url = MediaUrl::make($path, 'uploads');
            if ($url) {
                $urls[] = $url;
            }
        }

        foreach ((array) ($this->gallery_urls ?? []) as $url) {
            if (is_string($url) && trim($url) !== '') {
                $resolved = MediaUrl::make($url, 'uploads');
                if ($resolved) {
                    $urls[] = $resolved;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    public function hasActiveSponsorship(): bool
    {
        if (! $this->is_sponsored) {
            return false;
        }

        if ($this->sponsored_until && $this->sponsored_until->isPast()) {
            return false;
        }

        return true;
    }

    public function scopeActivelySponsored(Builder $query): Builder
    {
        return $query->where('is_sponsored', true)
            ->where(function (Builder $q) {
                $q->whereNull('sponsored_until')
                    ->orWhere('sponsored_until', '>', now());
            });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        return $this->where($field, $value)
            ->where(function (Builder $q) {
                $q->where('is_published', true)->orWhereNull('is_published');
            })
            ->first();
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => strip_tags((string) $this->description),
            'license_type' => $this->license_type,
            'primary_language' => $this->primary_language,
            'is_published' => $this->is_published,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_published;
    }

    public function seoTitle(): string
    {
        return app(SeoManager::class)->alternativeTitle($this);
    }

    public function seoDescription(): string
    {
        return app(SeoManager::class)->alternativeDescription($this);
    }

    public function hasBrokenLinks(): bool
    {
        return $this->repo_reachable === false || $this->website_reachable === false;
    }

    public function recalculateHealthScore(): float
    {
        return app(HealthHistoryService::class)->recalculate($this);
    }
}
