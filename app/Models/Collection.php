<?php

namespace App\Models;

use App\Services\WebhookDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Collection extends Model
{
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

    public function seoTitle(): string
    {
        if ($this->meta_title) {
            return $this->meta_title;
        }

        return $this->name.' | Alternova';
    }

    public function seoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        $desc = trim((string) $this->description);
        if ($desc !== '') {
            return str($desc)->limit(155)->toString();
        }

        return 'Curated open-source alternatives: '.$this->name;
    }
}
