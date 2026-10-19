<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSortOrder;
use App\Services\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Collection extends Model
{
    use HasAutoSortOrder;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'intro_html',
        'cover_image_url',
        'cover_path',
        'is_published',
        'is_featured',
        'sort_order',
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
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::created(function (Collection $collection) {
            try {
                if ($collection->is_published) {
                    $dispatcher = app(WebhookDispatcher::class);
                    $dispatcher->dispatch(
                        Webhook::EVENT_COLLECTION_PUBLISHED,
                        $dispatcher->collectionPublishedPayload($collection)
                    );
                }
            } catch (\Throwable) {
            }
        });

        static::updated(function (Collection $collection) {
            try {
                if ($collection->wasChanged('is_published') && $collection->is_published) {
                    $dispatcher = app(WebhookDispatcher::class);
                    $dispatcher->dispatch(
                        Webhook::EVENT_COLLECTION_PUBLISHED,
                        $dispatcher->collectionPublishedPayload($collection)
                    );
                }
            } catch (\Throwable) {
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class)->orderBy('position');
    }

    public function alternatives(): BelongsToMany
    {
        return $this->belongsToMany(
            OpenSourceAlternative::class,
            'collection_items',
            'collection_id',
            'open_source_alternative_id'
        )
            ->withPivot(['position', 'note'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    public function coverUrl(): ?string
    {
        if ($this->cover_path) {
            return Storage::disk('public')->url($this->cover_path);
        }

        return $this->cover_image_url ?: null;
    }

    public function canonicalUrl(): string
    {
        if (filled($this->canonical_url)) {
            return (string) $this->canonical_url;
        }

        return route('collections.show', $this->slug);
    }

    public function seoTitle(): string
    {
        if (filled($this->meta_title)) {
            return (string) $this->meta_title;
        }

        $site = config('app.name', 'Alternova');
        try {
            $site = app(\App\Services\SeoManager::class)->siteName();
        } catch (\Throwable) {
        }

        return $this->name.' · Collection · '.$site;
    }

    public function seoDescription(): string
    {
        if (filled($this->meta_description)) {
            return Str::limit((string) $this->meta_description, 160);
        }

        $desc = trim((string) $this->description);
        if ($desc !== '') {
            return Str::limit($desc, 155);
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->intro_html)) ?? '');
        if ($plain !== '') {
            return Str::limit($plain, 155);
        }

        return 'Curated open-source alternatives: '.$this->name;
    }
}
