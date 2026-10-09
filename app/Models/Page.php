<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'template',
        'body_html',
        'excerpt',
        'is_published',
        'show_in_footer',
        'show_in_nav',
        'sort_order',
        'meta_title',
        'meta_description',
        'robots_meta',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_in_footer' => 'boolean',
        'show_in_nav' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if (empty($page->slug) && $page->title) {
                $page->slug = Str::slug($page->title);
            }
            if ($page->is_published && ! $page->published_at) {
                $page->published_at = now();
            }
        });
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
     * Public URL — always /{slug} (e.g. /about, /team), never /p/{slug}.
     */
    public function publicUrl(): string
    {
        $slug = trim((string) $this->slug, '/');
        if ($slug === '') {
            return url('/');
        }

        $reserved = ['about', 'privacy', 'terms', 'disclosure'];
        if (in_array($slug, $reserved, true) && Route::has($slug)) {
            return route($slug);
        }

        return url('/'.$slug);
    }

    public function seoTitle(): string
    {
        return app(\App\Services\SeoManager::class)->cmsPageTitle($this);
    }

    public function seoDescription(): string
    {
        return app(\App\Services\SeoManager::class)->cmsPageDescription($this);
    }
}
