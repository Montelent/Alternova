<?php

namespace App\Livewire;

use App\Models\Collection;
use App\Services\SeoManager;
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
            ->values();

        $seo = app(SeoManager::class);
        $social = $seo->collectionSocial($collection);

        $title = $social['title'];
        $description = $social['description'];
        $canonical = $social['url'];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $collection->name,
            'description' => $description,
            'url' => $canonical,
            'mainEntity' => [
                '@type' => 'ItemList',
                'numberOfItems' => $items->count(),
                'itemListElement' => $items->values()->map(function ($item, $i) {
                    $alt = $item->alternative;

                    return [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $alt->name,
                        'url' => route('alternatives.show', $alt),
                    ];
                })->all(),
            ],
        ];

        return view('livewire.collection-show', [
            'collection' => $collection,
            'items' => $items,
            'schema' => $schema,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogType' => $social['type'],
            'ogTitle' => $social['title'],
            'ogDescription' => $social['description'],
            'ogImage' => $social['image'],
        ]);
    }
}
