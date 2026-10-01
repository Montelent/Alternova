<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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

    public function publicUrl(): string
    {
        $reserved = ['about', 'privacy', 'terms', 'disclosure'];
        if (in_array($this->slug, $reserved, true) && \Illuminate\Support\Facades\Route::has($this->slug)) {
            return route($this->slug);
        }

        return url('/p/'.$this->slug);
    }

    public function seoTitle(): string
    {
        return $this->meta_title ?: ($this->title.' | Alternova');
    }

    public function seoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return str(strip_tags((string) $this->body_html))->limit(155)->toString();
    }
}
