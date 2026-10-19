<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Google Rich Results–safe SoftwareApplication nodes.
 *
 * Google requires at least two of: offers, aggregateRating, applicationCategory, operatingSystem.
 * @see https://developers.google.com/search/docs/appearance/structured-data/software-app
 */
class SoftwareApplicationSchema
{
    /**
     * @param  array{
     *   name: string,
     *   url?: string|null,
     *   description?: string|null,
     *   license?: string|null,
     *   website?: string|null,
     *   repo?: string|null,
     *   free?: bool,
     *   category?: string|null,
     *   os?: string|null,
     *   id?: string|null,
     * }  $data
     * @return array<string, mixed>
     */
    public static function make(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        $free = (bool) ($data['free'] ?? true);

        // Google accepts values like BusinessApplication, UtilitiesApplication, BrowserApplication…
        $category = trim((string) ($data['category'] ?? ''));
        if ($category === '' || $category === 'OpenSourceSoftware') {
            $category = $free ? 'UtilitiesApplication' : 'BusinessApplication';
        }

        $os = trim((string) ($data['os'] ?? ''));
        if ($os === '') {
            $os = 'Cross-platform';
        }

        $node = [
            '@type' => 'SoftwareApplication',
            'name' => $name !== '' ? $name : 'Software',
            'applicationCategory' => $category,
            'operatingSystem' => $os,
        ];

        if (! empty($data['id'])) {
            $node['@id'] = (string) $data['id'];
        }

        if (! empty($data['url'])) {
            $node['url'] = (string) $data['url'];
        }

        if (! empty($data['description'])) {
            $node['description'] = Str::limit(strip_tags((string) $data['description']), 300);
        }

        // Always satisfy the "two of four" rule: category + OS already count.
        // Free / open-source: explicit $0 Offer (helps rich results).
        if ($free) {
            $node['offers'] = [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ];
        }

        if (! empty($data['license'])) {
            $node['license'] = (string) $data['license'];
        }

        $sameAs = [];
        if (! empty($data['website'])) {
            $sameAs[] = (string) $data['website'];
        }
        if (! empty($data['repo'])) {
            $sameAs[] = (string) $data['repo'];
        }
        if ($sameAs !== []) {
            $node['sameAs'] = array_values(array_unique($sameAs));
        }

        return $node;
    }
}
