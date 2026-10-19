<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use Illuminate\Support\Str;

class AlternativeJsonLd
{
    /**
     * @return list<array<string, mixed>>
     */
    public function build(OpenSourceAlternative $alt, $prop, string $canonical): array
    {
        $site = rtrim((string) config('app.url'), '/');
        $siteName = config('app.name', 'Alternova');
        try {
            $siteName = app(SeoManager::class)->siteName();
        } catch (\Throwable) {
        }

        $desc = Str::limit(strip_tags((string) $alt->description), 300);

        $software = [
            '@type' => 'SoftwareApplication',
            '@id' => $canonical.'#software',
            'name' => $alt->name,
            'description' => $desc,
            'url' => $canonical,
            'applicationCategory' => 'OpenSourceSoftware',
            'operatingSystem' => 'Cross-platform',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ],
        ];

        if (filled($alt->license_type)) {
            $software['license'] = (string) $alt->license_type;
        }

        $sameAs = [];
        if (filled($alt->website_url)) {
            $sameAs[] = (string) $alt->website_url;
        }
        if (filled($alt->repo_url)) {
            $sameAs[] = (string) $alt->repo_url;
        }
        if ($sameAs !== []) {
            $software['sameAs'] = $sameAs;
        }

        if ($prop && filled($prop->name ?? null)) {
            $related = [
                '@type' => 'SoftwareApplication',
                'name' => (string) $prop->name,
                'applicationCategory' => 'BusinessApplication',
            ];
            if (filled($prop->website_url ?? null)) {
                $related['url'] = (string) $prop->website_url;
            }
            $software['isRelatedTo'] = $related;
        }

        $webPage = [
            '@type' => 'WebPage',
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => $alt->name,
            'description' => $desc,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => $site.'/#website',
                'name' => $siteName,
                'url' => $site,
            ],
            'mainEntity' => ['@id' => $canonical.'#software'],
            'breadcrumb' => ['@id' => $canonical.'#breadcrumb'],
        ];

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $site],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Alternatives', 'item' => route('finder')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $alt->name, 'item' => $canonical],
            ],
        ];

        return [
            [
                '@context' => 'https://schema.org',
                '@graph' => [$webPage, $software, $breadcrumb],
            ],
        ];
    }
}
