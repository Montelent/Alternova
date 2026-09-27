<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Facades\URL;

class SitemapService
{
    public function generate(): string
    {
        $urls = collect();

        $urls->push([
            'loc' => route('home'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ]);

        $urls->push([
            'loc' => route('finder'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ]);

        $urls->push([
            'loc' => route('domains'),
            'lastmod' => now()->toAtomString(),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ]);

        OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderBy('updated_at', 'desc')
            ->chunk(200, function ($alternatives) use ($urls) {
                foreach ($alternatives as $alt) {
                    $urls->push([
                        'loc' => route('alternatives.show', $alt),
                        'lastmod' => $alt->updated_at->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ]);
                }
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
