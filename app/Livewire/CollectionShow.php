<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Services\SeoManager;
use App\Support\SoftwareApplicationSchema;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CollectionShow extends Component
{
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;

        if (! Schema::hasTable('collections')) {
            abort(404);
        }

        $exists = Collection::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->exists();

        if (! $exists) {
            abort(404);
        }
    }

    public function render()
    {
        $collection = Collection::query()
            ->where('slug', $this->slug)
            ->where('is_published', true)
            ->with(['items.alternative.proprietaryTool', 'items.alternative.repoMetric'])
            ->firstOrFail();

        $items = $collection->items
            ->filter(fn ($item) => $item->alternative && $item->alternative->is_published)
            ->sortBy(fn ($item) => (int) ($item->position ?? 0))
            ->values();

        $seo = app(SeoManager::class);
        $social = $seo->collectionSocial($collection);

        $title = $social['title'];
        $description = $social['description'];
        $canonical = $social['url'];
        $cover = $collection->coverUrl() ?: $social['image'];

        $jsonLd = $this->buildJsonLd($collection, $items, $canonical, $description, $cover);

        return view('livewire.collection-show', [
            'collection' => $collection,
            'items' => $items,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $collection->robots_meta ?: null,
            'ogType' => $social['type'],
            'ogTitle' => $social['title'],
            'ogDescription' => $social['description'],
            'ogImage' => $cover,
            'jsonLd' => $jsonLd,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection  $items
     * @return list<array<string, mixed>>
     */
    protected function buildJsonLd(Collection $collection, $items, string $canonical, string $description, ?string $cover): array
    {
        $site = rtrim((string) config('app.url'), '/');
        $siteName = config('app.name', 'Alternova');
        try {
            $siteName = app(SeoManager::class)->siteName();
        } catch (\Throwable) {
        }

        $listElements = [];
        foreach ($items->values() as $i => $item) {
            $alt = $item->alternative;
            $entry = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $alt->name,
                'url' => route('alternatives.show', $alt),
                'item' => SoftwareApplicationSchema::make([
                    'name' => $alt->name,
                    'url' => route('alternatives.show', $alt),
                    'description' => $alt->description,
                    'license' => $alt->license_type,
                    'website' => $alt->website_url,
                    'repo' => $alt->repo_url,
                    'free' => true,
                    'category' => 'UtilitiesApplication',
                    'os' => 'Cross-platform',
                ]),
            ];
            $listElements[] = $entry;
        }

        $collectionPage = [
            '@type' => 'CollectionPage',
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => $collection->name,
            'description' => $description,
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => $site.'/#website',
                'name' => $siteName,
                'url' => $site,
            ],
            'mainEntity' => [
                '@id' => $canonical.'#itemlist',
            ],
        ];

        if ($cover) {
            $collectionPage['image'] = [$cover];
            $collectionPage['primaryImageOfPage'] = [
                '@type' => 'ImageObject',
                'url' => $cover,
            ];
        }

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $site,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Collections',
                    'item' => route('collections.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $collection->name,
                    'item' => $canonical,
                ],
            ],
        ];

        $itemList = [
            '@type' => 'ItemList',
            '@id' => $canonical.'#itemlist',
            'name' => $collection->name,
            'description' => $description,
            'url' => $canonical,
            'numberOfItems' => count($listElements),
            'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
            'itemListElement' => $listElements,
        ];

        return [
            [
                '@context' => 'https://schema.org',
                '@graph' => [$collectionPage, $itemList, $breadcrumb],
            ],
        ];
    }
}
