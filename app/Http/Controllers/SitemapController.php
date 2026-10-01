<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\Page;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');

        $includeDomains = SiteSetting::getBool('seo_sitemap_domains', true);
        $includeCompare = SiteSetting::getBool('seo_sitemap_compare', false);

        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base.'/alternatives', 'changefreq' => 'daily', 'priority' => '0.9'],
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

        try {
            if (Schema::hasTable('pages')) {
                $pages = Page::query()->published()->orderBy('sort_order')->get(['slug', 'updated_at', 'noindex']);
                foreach ($pages as $p) {
                    if (! empty($p->noindex)) {
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
            $urls[] = ['loc' => $base.'/about', 'changefreq' => 'monthly', 'priority' => '0.5'];
            $urls[] = ['loc' => $base.'/privacy', 'changefreq' => 'yearly', 'priority' => '0.3'];
            $urls[] = ['loc' => $base.'/terms', 'changefreq' => 'yearly', 'priority' => '0.3'];
            $urls[] = ['loc' => $base.'/disclosure', 'changefreq' => 'yearly', 'priority' => '0.3'];
        }

        try {
            if (Schema::hasTable('collections')) {
                $collections = Collection::query()
                    ->where('is_published', true)
                    ->orderByDesc('updated_at')
                    ->get(['slug', 'updated_at']);

                foreach ($collections as $c) {
                    $urls[] = [
                        'loc' => $base.'/collections/'.$c->slug,
                        'lastmod' => optional($c->updated_at)->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.75',
                    ];
                }
            }
        } catch (\Throwable) {
        }

        // Canonical proprietary URLs: /alternativesto/{slug} (not /tools/)
        $tools = ProprietaryTool::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'robots_meta']);

        foreach ($tools as $tool) {
            if ($this->isNoindex($tool->robots_meta ?? null)) {
                continue;
            }
            $urls[] = [
                'loc' => $base.'/alternativesto/'.$tool->slug,
                'lastmod' => optional($tool->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.85',
            ];
        }

        $alternatives = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at', 'robots_meta']);

        foreach ($alternatives as $alt) {
            if ($this->isNoindex($alt->robots_meta ?? null)) {
                continue;
            }
            $urls[] = [
                'loc' => $base.'/alternatives/'.$alt->slug,
                'lastmod' => optional($alt->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    protected function isNoindex(?string $robots): bool
    {
        if (! $robots) {
            return false;
        }

        return str_contains(strtolower($robots), 'noindex');
    }
}
