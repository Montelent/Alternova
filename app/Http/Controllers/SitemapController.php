<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\Page;
use App\Models\ProprietaryTool;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');

        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base.'/alternatives', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/leaderboard', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $base.'/trending', 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $base.'/collections', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $base.'/whats-new', 'changefreq' => 'daily', 'priority' => '0.7'],
            ['loc' => $base.'/alternatives/compare', 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $base.'/domains', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $base.'/suggest', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $base.'/suggest/collection', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $base.'/contact', 'changefreq' => 'monthly', 'priority' => '0.4'],
        ];

        try {
            if (Schema::hasTable('pages')) {
                $pages = Page::query()->published()->orderBy('sort_order')->get(['slug', 'updated_at']);
                foreach ($pages as $p) {
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

        $tools = ProprietaryTool::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        foreach ($tools as $tool) {
            $urls[] = [
                'loc' => $base.'/tools/'.$tool->slug,
                'lastmod' => optional($tool->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.75',
            ];
        }

        $alternatives = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        foreach ($alternatives as $alt) {
            $urls[] = [
                'loc' => $base.'/alternatives/'.$alt->slug,
                'lastmod' => optional($alt->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
