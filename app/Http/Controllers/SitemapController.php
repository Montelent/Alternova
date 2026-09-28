<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('app.url'), '/');

        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base.'/alternatives', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/alternatives/compare', 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $base.'/domains', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['loc' => $base.'/about', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $base.'/contact', 'changefreq' => 'monthly', 'priority' => '0.4'],
            ['loc' => $base.'/privacy', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => $base.'/terms', 'changefreq' => 'yearly', 'priority' => '0.3'],
            ['loc' => $base.'/suggest', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

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
