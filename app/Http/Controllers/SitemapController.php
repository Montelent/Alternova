<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Models\Page;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Support\CategoryCatalog;
use App\Services\SeoManager;
use App\Support\CanonicalUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /** Sitemap index listing all child sitemaps. */
    public function index(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
        $now = now()->toAtomString();

        $sitemaps = [
            ['loc' => $base.'/sitemap-static.xml', 'lastmod' => $now],
            ['loc' => $base.'/sitemap-categories.xml', 'lastmod' => $now],
        ];

        // Keep every child sitemap below the protocol's 50,000 URL limit.
        $sitemapSources = [
            ['path' => '/sitemap-pages.xml', 'count' => $this->indexablePages()->count()],
            ['path' => '/sitemap-tools.xml', 'count' => $this->indexableTools()->count()],
            ['path' => '/sitemap-alternatives.xml', 'count' => $this->indexableAlternatives()->count()],
            ['path' => '/sitemap-collections.xml', 'count' => $this->indexableCollections()->count()],
        ];

        foreach ($sitemapSources as $source) {
            $pages = max(1, (int) ceil($source['count'] / $this->sitemapPageSize()));
            for ($page = 1; $page <= $pages; $page++) {
                $sitemaps[] = [
                    'loc' => $base.$source['path'].($page > 1 ? '?page='.$page : ''),
                    'lastmod' => $now,
                ];
            }
        }

        $xml = view('sitemap-index', ['sitemaps' => $sitemaps])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function staticPages(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
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
        ];

        if ($includeDomains) {
            $urls[] = ['loc' => $base.'/domains', 'changefreq' => 'weekly', 'priority' => '0.7'];
        }

        if ($includeCompare) {
            $urls[] = ['loc' => $base.'/alternatives/compare', 'changefreq' => 'weekly', 'priority' => '0.5'];
        }

        return $this->urlset($urls);
    }

    public function pages(Request $request): Response
    {
        $urls = [];

        try {
            if (Schema::hasTable('pages')) {
                $pages = $this->indexablePages()->orderBy('id')->forPage($this->sitemapPage($request), $this->sitemapPageSize())->get(['slug', 'updated_at', 'robots_meta']);
                foreach ($pages as $p) {
                    if ($this->isNoindex($p->robots_meta ?? null)) {
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
            $base = rtrim(CanonicalUrl::base($request), '/');
            foreach (['about', 'privacy', 'terms', 'disclosure'] as $slug) {
                $urls[] = ['loc' => $base.'/'.$slug, 'changefreq' => 'yearly', 'priority' => '0.4'];
            }
        }

        return $this->urlset($urls);
    }

    public function tools(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
        $urls = [];

        $this->indexableTools()
            ->orderBy('id')
            ->forPage($this->sitemapPage($request), $this->sitemapPageSize())
            ->get(['slug', 'updated_at', 'robots_meta', 'canonical_url'])
            ->each(function ($tool) use (&$urls, $base) {
                if ($this->isNoindex($tool->robots_meta ?? null)) {
                    return;
                }
                $urls[] = [
                    'loc' => app(SeoManager::class)->canonicalOverride($tool->canonical_url ?? null, $base.'/alternativesto/'.$tool->slug),
                    'lastmod' => optional($tool->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.85',
                ];
            });

        return $this->urlset($urls);
    }

    public function alternatives(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
        $urls = [];

        $this->indexableAlternatives()
            ->orderBy('id')
            ->forPage($this->sitemapPage($request), $this->sitemapPageSize())
            ->get(['slug', 'updated_at', 'robots_meta', 'canonical_url'])
            ->each(function ($alt) use (&$urls, $base) {
                if ($this->isNoindex($alt->robots_meta ?? null)) {
                    return;
                }
                $urls[] = [
                    'loc' => app(SeoManager::class)->canonicalOverride($alt->canonical_url ?? null, $base.'/alternatives/'.$alt->slug),
                    'lastmod' => optional($alt->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            });

        return $this->urlset($urls);
    }

    public function categories(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
        $urls = [];

        // Category hubs
        try {
            $names = OssCategory::activeNames();
            if ($names === []) {
                $names = CategoryCatalog::names();
            }
            foreach ($names as $name) {
                $hasIndexableAlternatives = $this->indexableAlternatives()
                    ->withAnyTags([$name], 'category')
                    ->exists();
                if (! $hasIndexableAlternatives) {
                    continue;
                }
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
            $langs = $this->indexableAlternatives()
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
            $lics = $this->indexableAlternatives()
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

    public function collections(Request $request): Response
    {
        $base = rtrim(CanonicalUrl::base($request), '/');
        $urls = [];

        try {
            if (Schema::hasTable('collections')) {
                $this->indexableCollections()
                    ->orderBy('id')
                    ->forPage($this->sitemapPage($request), $this->sitemapPageSize())
                    ->get(['slug', 'updated_at', 'robots_meta', 'canonical_url'])
                    ->each(function ($c) use (&$urls, $base) {
                        if ($this->isNoindex($c->robots_meta ?? null)) {
                            return;
                        }
                        $urls[] = [
                            'loc' => app(SeoManager::class)->canonicalOverride($c->canonical_url ?? null, $base.'/collections/'.$c->slug),
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



    protected function indexablePages()
    {
        return Page::query()->published()
            ->where(function ($query) {
                $query->whereNull('robots_meta')
                    ->orWhereRaw('LOWER(robots_meta) NOT LIKE ?', ['%noindex%']);
            });
    }

    protected function indexableTools()
    {
        return ProprietaryTool::query()->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('robots_meta')
                    ->orWhereRaw('LOWER(robots_meta) NOT LIKE ?', ['%noindex%']);
            });
    }

    protected function indexableAlternatives()
    {
        return OpenSourceAlternative::query()->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('robots_meta')
                    ->orWhereRaw('LOWER(robots_meta) NOT LIKE ?', ['%noindex%']);
            });
    }

    protected function indexableCollections()
    {
        return Collection::query()->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('robots_meta')
                    ->orWhereRaw('LOWER(robots_meta) NOT LIKE ?', ['%noindex%']);
            });
    }

    protected function sitemapPageSize(): int
    {
        return max(1, min(50000, (int) config('seo.sitemap_page_size', 45000)));
    }

    protected function sitemapPage(Request $request): int
    {
        return max(1, min(1000000, $request->integer('page', 1)));
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
