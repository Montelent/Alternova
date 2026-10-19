<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Support\SoftwareApplicationSchema;
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

        $software = SoftwareApplicationSchema::make([
            'name' => $alt->name,
            'url' => $canonical,
            'description' => $desc,
            'license' => $alt->license_type,
            'website' => $alt->website_url,
            'repo' => $alt->repo_url,
            'free' => true,
            'category' => 'UtilitiesApplication',
            'os' => 'Cross-platform',
            'id' => $canonical.'#software',
        ]);

        if ($prop && filled($prop->name ?? null)) {
            $software['isRelatedTo'] = SoftwareApplicationSchema::make([
                'name' => (string) $prop->name,
                'url' => $prop->website_url ?: null,
                'website' => $prop->website_url ?? null,
                'free' => false,
                'category' => 'BusinessApplication',
                'os' => 'Cross-platform',
            ]);
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
