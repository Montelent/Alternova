<?php

namespace App\Livewire;

use App\Models\ProprietaryTool;
use App\Models\SlugRedirect;
use App\Services\ProprietaryPageCopy;
use App\Services\SeoManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;
use Livewire\Component;

class ProprietaryToolShow extends Component
{
    public ProprietaryTool $tool;

    public function mount(string $slug): void
    {
        $requestedSlug = trim($slug);

        $record = ProprietaryTool::query()
            ->where('slug', $requestedSlug)
            ->where('is_published', true)
            ->first();

        if (! $record) {
            try {
                $redirect = null;
                if (class_exists(SlugRedirect::class)) {
                    $redirect = SlugRedirect::query()
                        ->where('old_slug', $requestedSlug)
                        ->where('model_type', 'tool')
                        ->first();
                }

                if ($redirect) {
                    $target = ProprietaryTool::query()
                        ->where('slug', $redirect->new_slug)
                        ->where('is_published', true)
                        ->first();

                    if ($target) {
                        throw new HttpResponseException(
                            redirect()->to(route('alternativesto.show', $target->slug), 301)
                        );
                    }
                }
            } catch (HttpResponseException $e) {
                throw $e;
            } catch (\Throwable) {
            }

            abort(404);
        }

        $this->tool = $record;
    }

    public function render()
    {
        $tool = $this->tool;
        $alternatives = $tool->linkedAlternatives();
        $count = $alternatives->count();

        $categoryList = [];
        foreach ($alternatives as $alt) {
            if ($alt->relationLoaded('tags')) {
                foreach ($alt->tags->where('type', 'category') as $tag) {
                    $categoryList[(string) $tag->name] = true;
                }
            }
        }
        $categoryList = array_keys($categoryList);

        $copy = app(ProprietaryPageCopy::class)->build($tool, $alternatives, $categoryList);

        $seo = app(SeoManager::class);
        $title = $seo->toolTitle($tool, $count);

        $description = filled($tool->meta_description)
            ? (string) $tool->meta_description
            : ($copy['meta_description'] ?? $seo->toolDescription($tool));

        $canonical = $tool->canonical_url ?: route('alternativesto.show', $tool->slug);

        $social = $seo->toolSocial($tool, $count, $copy['heading'] ?? null);
        if (! filled($tool->og_description) && ! empty($copy['meta_description'])) {
            $social['description'] = $copy['meta_description'];
        }

        $jsonLd = $this->buildJsonLd($tool, $alternatives, $count, $canonical, $description);

        return view('livewire.proprietary-tool-show', [
            'alternatives' => $alternatives,
            'count' => $count,
            'heading' => $copy['heading'],
            'kicker' => $copy['kicker'],
            'introHtml' => $copy['intro_html'],
            'bodyHtml' => $copy['body_html'],
            'categoryList' => $categoryList,
        ])->layout('layouts.app', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $tool->robots_meta ?: null,
            'ogType' => $social['type'],
            'ogTitle' => $social['title'],
            'ogDescription' => $social['description'],
            'ogImage' => $social['image'],
            'jsonLd' => $jsonLd,
        ]);
    }

    protected function buildJsonLd($tool, $alternatives, int $count, string $canonical, string $description): array
    {
        $listElements = [];
        $pos = 1;
        foreach ($alternatives as $alt) {
            $listElements[] = [
                '@type' => 'ListItem',
                'position' => $pos++,
                'url' => route('alternatives.show', $alt->slug),
                'name' => $alt->name,
            ];
        }

        $graph = [
            [
                '@type' => 'CollectionPage',
                '@id' => $canonical.'#webpage',
                'url' => $canonical,
                'name' => $count.' Open Source Alternatives to '.$tool->name,
                'description' => Str::limit(strip_tags($description), 160),
                'isPartOf' => ['@type' => 'WebSite', 'name' => config('app.name'), 'url' => url('/')],
                'about' => [
                    '@type' => 'SoftwareApplication',
                    'name' => $tool->name,
                    'url' => $tool->website_url ?: $canonical,
                    'applicationCategory' => 'BusinessApplication',
                ],
            ],
            [
                '@type' => 'ItemList',
                '@id' => $canonical.'#itemlist',
                'name' => 'Open source alternatives to '.$tool->name,
                'numberOfItems' => $count,
                'itemListElement' => $listElements,
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Alternatives', 'item' => route('finder')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $tool->name, 'item' => $canonical],
                ],
            ],
        ];

        return [['@context' => 'https://schema.org', '@graph' => $graph]];
    }
}
