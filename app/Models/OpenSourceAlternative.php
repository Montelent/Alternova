<?php

namespace App\Models;

use App\Services\HealthHistoryService;
use App\Services\SeoManager;
use App\Services\WatchlistService;
use App\Services\WebhookDispatcher;
use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class OpenSourceAlternative extends Model
{
    use HasFactory, SoftDeletes, HasTags, Searchable;

    protected $fillable = [
        'proprietary_tool_id',
        'name',
        'slug',
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

    protected static function booted(): void
    {
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
        )->withPivot('position')->withTimestamps()->orderByPivot('position');
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * All screenshot URLs: uploaded files (gallery_paths) + external URLs (gallery_urls).
     *
     * @return list<string>
     */
    public function galleryImageUrls(): array
    {
        $urls = [];

        foreach ((array) ($this->gallery_paths ?? []) as $path) {
            if (! $path) {
                continue;
            }
            $url = MediaUrl::make(is_string($path) ? $path : null, 'uploads');
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

        if ($this->sponsored_until === null) {
            return true;
        }

        return $this->sponsored_until->isFuture();
    }

    public function scopeActivelySponsored(Builder $query): Builder
    {
        return $query
            ->where('is_sponsored', true)
            ->where(function (Builder $q) {
                $q->whereNull('sponsored_until')
                    ->orWhere('sponsored_until', '>', now());
            });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        $record = static::query()
            ->where($field, $value)
            ->where('is_published', true)
            ->first();

        if ($record) {
            return $record;
        }

        try {
            $redirect = SlugRedirect::query()->where('old_slug', $value)->first();
            if ($redirect) {
                return static::query()
                    ->where('slug', $redirect->new_slug)
                    ->where('is_published', true)
                    ->first();
            }
        } catch (\Throwable) {
        }

        return null;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'license_type' => $this->license_type,
            'self_host_difficulty' => $this->self_host_difficulty,
            'primary_language' => $this->primary_language,
            'overall_health_score' => $this->overall_health_score,
            'proprietary_tool_name' => $this->proprietaryTool?->name,
            'tags' => $this->tags->pluck('name')->toArray(),
        ];
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
        if ($this->links_checked_at === null) {
            return false;
        }

        return $this->repo_reachable === false || $this->website_reachable === false;
    }

    public function recalculateHealthScore(): float
    {
        $metric = RepoMetric::query()
            ->where('open_source_alternative_id', $this->id)
            ->first();

        $previous = (float) ($this->overall_health_score ?? 0);

        if (! $metric) {
            $this->forceFill(['overall_health_score' => 0])->save();

            return 0.0;
        }

        $stars = max((int) $metric->github_stars, 0);
        $forks = max((int) $metric->github_forks, 0);
        $issues = max((int) $metric->open_issues, 0);

        $starsScore = min(log10(max($stars, 1)) * 15, 40);
        $forksScore = min(log10(max($forks, 1)) * 10, 20);
        $issuesScore = $issues < 50 ? 15 : max(0, 15 - ($issues / 20));

        $recencyScore = 0;
        if ($metric->last_commit_at) {
            if ($metric->last_commit_at->gt(now()->subMonths(3))) {
                $recencyScore = 25;
            } elseif ($metric->last_commit_at->gt(now()->subYear())) {
                $recencyScore = 10;
            }
        }

        $score = round(min($starsScore + $forksScore + $issuesScore + $recencyScore, 100), 2);

        $this->forceFill(['overall_health_score' => $score])->save();

        try {
            $this->setRelation('repoMetric', $metric);
            app(HealthHistoryService::class)->record($this, $score);
        } catch (\Throwable) {
        }

        try {
            if ($previous > 0) {
                app(WatchlistService::class)->notifyHealthDrop($this, $previous, $score);
            }
        } catch (\Throwable) {
        }

        try {
            $drop = $previous - $score;
            if ($previous > 0 && $drop >= 5.0) {
                $dispatcher = app(WebhookDispatcher::class);
                $dispatcher->dispatch(
                    Webhook::EVENT_HEALTH_DROP,
                    $dispatcher->healthDropPayload($this, $previous, $score)
                );
            }
        } catch (\Throwable) {
        }

        return $score;
    }
}
