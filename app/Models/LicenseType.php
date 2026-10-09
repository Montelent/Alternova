<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LicenseType extends Model
{
    use HasAutoSortOrder;

    protected $fillable = [
        'name',
        'slug',
        'spdx_id',
        'description',
        'is_osi_approved',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_osi_approved' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (LicenseType $license) {
            if (empty($license->slug) && $license->name) {
                $license->slug = Str::slug($license->name);
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
            return ['MIT', 'Apache-2.0', 'AGPL-3.0', 'GPL-3.0', 'BSD-3-Clause', 'MPL-2.0', 'BSL-1.1', 'Other'];
        }
    }
}
