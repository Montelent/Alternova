<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Models\Page;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Support\CategoryCatalog;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /** Sitemap index listing all child sitemaps. */
    public function index(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $now = now()->toAtomString();

        $sitemaps = [
            ['loc' => $base.'/sitemap-static.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-pages.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-tools.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-alternatives.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-categories.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-collections.xml', 'lastmod' => $now],
        ];

        $xml = view('sitemap-index', ['sitemaps' => $sitemaps])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function staticPages(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $includeDomains = SiteSetting::getBool('seo_sitemap_domains', true);
        $includeCompare = SiteSetting::getBool('seo_sitemap_compare', false);

        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => now()->toAtomString()],
            ['loc' => $base.'/alternatives', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/browse/categories', 'changefreq' => 'weekly', 'priority' => '0.75'],
            ['loc' => $base.'/browse/languages', 'changefreq' => 'weekly', 'priority' => '0.75'],
            ['loc' => $base.'/browse/licenses', 'changefreq' => 'weekly', 'priority' => '0.75'],
            ['loc' => $base.'/leaderboard', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $base.'/trending', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $base.'/collections', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $base.'/whats-new', 'changefreq' => 'daily', 'priority' => '0.7'],
            ['loc' => $base.'/suggest', 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => $base.'/contact', 'changefreq' => 'monthly', 'priority' => '0.3'],
        ];

        if ($includeDomains) {
            $urls[] = ['loc' => $base.'/domains', 'changefreq' => 'weekly', 'priority' => '0.7'];
        }

        if ($includeCompare) {
            $urls[] = ['loc' => $base.'/alternatives/compare', 'changefreq' => 'weekly', 'priority' => '0.5'];
        }

        return $this->urlset($urls);
    }

    public function pages(): Response
    {
        $urls = [];

        try {
            if (Schema::hasTable('pages')) {
                $pages = Page::query()->published()->orderBy('sort_order')->get(['slug', 'updated_at', 'noindex', 'robots_meta']);
                foreach ($pages as $p) {
                    if (! empty($p->noindex) || $this->isNoindex($p->robots_meta ?? null)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => rtrim($p->publicUrl(), '/'),
                        'lastmod' => optional($p->updated_at)->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.5',
                    ];
                }
            }
        } catch (\Throwable) {
            $base = rtrim((string) config('app.url'), '/');
            foreach (['about', 'privacy', 'terms', 'disclosure'] as $slug) {
                $urls[] = ['loc' => $base.'/'.$slug, 'changefreq' => 'yearly', 'priority' => '0.4'];
            }
        }

        return $this->urlset($urls);
    }

    public function tools(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        ProprietaryTool::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'robots_meta'])
            ->each(function ($tool) use (&$urls, $base) {
                if ($this->isNoindex($tool->robots_meta ?? null)) {
                    return;
                }
                $urls[] = [
                    'loc' => $base.'/alternativesto/'.$tool->slug,
                    'lastmod' => optional($tool->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.85',
                ];
            });

        return $this->urlset($urls);
    }

    public function alternatives(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'robots_meta'])
            ->each(function ($alt) use (&$urls, $base) {
                if ($this->isNoindex($alt->robots_meta ?? null)) {
                    return;
                }
                $urls[] = [
                    'loc' => $base.'/alternatives/'.$alt->slug,
                    'lastmod' => optional($alt->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        return $this->urlset($urls);
    }

    public function categories(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        // Category hubs
        try {
            $names = OssCategory::activeNames();
            if ($names === []) {
                $names = CategoryCatalog::names();
            }
            foreach ($names as $name) {
                $urls[] = [
                    'loc' => $base.'/browse/categories/'.Str::slug($name),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            }
        } catch (\Throwable) {
        }

        // Language hubs
        try {
            $langs = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->whereNotNull('primary_language')
                ->where('primary_language', '!=', '')
                ->distinct()
                ->pluck('primary_language');
            foreach ($langs as $lang) {
                $urls[] = [
                    'loc' => $base.'/browse/languages/'.Str::slug($lang),
                    'changefreq' => 'weekly',
                    'priority' => '0.65',
                ];
            }
        } catch (\Throwable) {
        }

        // License hubs
        try {
            $lics = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->whereNotNull('license_type')
                ->where('license_type', '!=', '')
                ->distinct()
                ->pluck('license_type');
            foreach ($lics as $lic) {
                $urls[] = [
                    'loc' => $base.'/browse/licenses/'.Str::slug($lic),
                    'changefreq' => 'weekly',
                    'priority' => '0.65',
                ];
            }
        } catch (\Throwable) {
        }

        return $this->urlset($urls);
    }

    public function collections(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $urls = [];

        try {
            if (Schema::hasTable('collections')) {
                Collection::query()
                    ->where('is_published', true)
                    ->orderByDesc('updated_at')
                    ->get(['slug', 'updated_at'])
                    ->each(function ($c) use (&$urls, $base) {
                        $urls[] = [
                            'loc' => $base.'/collections/'.$c->slug,
                            'lastmod' => optional($c->updated_at)->toAtomString(),
                            'changefreq' => 'weekly',
                            'priority' => '0.75',
                        ];
                    });
            }
        } catch (\Throwable) {
        }

        return $this->urlset($urls);
    }

    protected function urlset(array $urls): Response
    {
        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function isNoindex(?string $robots): bool
    {
        if (! $robots) {
            return false;
        }

        return str_contains(strtolower($robots), 'noindex');
    }
}
