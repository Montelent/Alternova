<?php

namespace App\Support;

use App\Models\LicenseType;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Models\ProprietaryTool;
use App\Support\CategoryCatalog;
use Illuminate\Support\Facades\Cache;
use Spatie\Tags\Tag;

/**
 * Shared short-lived caches for filter chips and search suggestions.
 */
class QueryCache
{
    public const FILTER_TTL = 600; // 10 minutes

    public const SUGGEST_TTL = 120; // 2 minutes

    /**
     * @return list<string>
     */
    public static function publishedLanguages(): array
    {
        return Cache::remember('finder.languages.v1', self::FILTER_TTL, function () {
            return OpenSourceAlternative::query()
                ->where('is_published', true)
                ->whereNotNull('primary_language')
                ->where('primary_language', '!=', '')
                ->distinct()
                ->orderBy('primary_language')
                ->pluck('primary_language')
                ->all();
        });
    }

    /**
     * @return list<array{id:int,name:string,slug:string}>
     */
    public static function toolsWithAlternatives(): array
    {
        return Cache::remember('finder.tools.v1', self::FILTER_TTL, function () {
            return ProprietaryTool::query()
                ->where('is_published', true)
                ->whereHas('publishedAlternatives')
                ->orderBy('name')
                ->get(['id', 'name', 'slug'])
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])
                ->all();
        });
    }

    /**
     * @return list<string>
     */
    public static function categoryNames(): array
    {
        return Cache::remember('finder.categories.v1', self::FILTER_TTL, function () {
            $names = OssCategory::activeNames();
            if ($names !== []) {
                return $names;
            }
            try {
                return Tag::query()->where('type', 'category')->orderBy('name')->pluck('name')->all();
            } catch (\Throwable) {
                return CategoryCatalog::names();
            }
        });
    }

    /**
     * @return list<string>
     */
    public static function licenseNames(): array
    {
        return Cache::remember('finder.licenses.v1', self::FILTER_TTL, function () {
            return LicenseType::activeNames();
        });
    }

    public static function bustCatalog(): void
    {
        foreach ([
            'finder.languages.v1',
            'finder.tools.v1',
            'finder.categories.v1',
            'finder.licenses.v1',
            'home.page.v1',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
