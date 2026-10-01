<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OssCategory extends Model
{
    protected $table = 'oss_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (OssCategory $cat) {
            if (empty($cat->slug) && $cat->name) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return list<string> */
    public static function activeNames(): array
    {
        try {
            return static::query()->active()->orderBy('sort_order')->orderBy('name')->pluck('name')->all();
        } catch (\Throwable) {
            return \App\Support\CategoryCatalog::names();
        }
    }
}
