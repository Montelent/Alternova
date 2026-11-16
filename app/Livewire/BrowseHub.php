<?php

namespace App\Livewire;

use App\Models\LicenseType;
use App\Models\OpenSourceAlternative;
use App\Models\OssCategory;
use App\Services\SeoManager;
use App\Support\CategoryCatalog;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Tags\Tag;

/**
 * Public SEO hubs: categories, languages, licenses (index + detail).
 */
class BrowseHub extends Component
{
    use WithPagination;

    /** categories | languages | licenses */
    public string $type = 'categories';

    public ?string $slug = null;

    public int $perPage = 12;

    public function mount(string $type, ?string $slug = null): void
    {
        $type = strtolower($type);
        abort_unless(in_array($type, ['categories', 'languages', 'licenses'], true), 404);
        $this->type = $type;
        $this->slug = $slug ? Str::lower($slug) : null;
    }

    public function render()
    {
        $seo = app(SeoManager::class);

        if ($this->slug === null) {
            return $this->renderIndex($seo);
        }

        return $this->renderShow($seo);
    }

    protected function renderIndex(SeoManager $seo)
    {
        $items = match ($this->type) {
            'categories' => $this->categoryIndexItems(),
            'languages' => $this->languageIndexItems(),
            'licenses' => $this->licenseIndexItems(),
            default => collect(),
        };

        $title = match ($this->type) {
            'categories' => 'Browse by category',
            'languages' => 'Browse by programming language',
            'licenses' => 'Browse by open-source license',
            default => 'Browse',
        };

        $description = match ($this->type) {
            'categories' => 'Explore open-source alternatives grouped by product category.',
            'languages' => 'Find self-hostable tools by primary programming language.',
            'licenses' => 'Filter open-source alternatives by license type.',
            default => 'Browse open-source alternatives.',
        };

        return view('livewire.browse-hub-index', [
            'type' => $this->type,
            'items' => $items,
            'heading' => $title,
        ])->layout('layouts.app', [
            'title' => $title.' | '.$seo->siteName(),
            'description' => $description,
            'canonical' => $seo->currentCanonicalUrl(),
            'jsonLd' => $this->buildHubSchema($title, $description, $items, $seo->currentCanonicalUrl()),
        ]);
    }

    protected function renderShow(SeoManager $seo)
    {
        $meta = $this->resolveShowMeta();
        abort_unless($meta !== null, 404);

        $query = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric', 'tags'])
            ->where('is_published', true);

        if ($this->type === 'categories') {
            $query->withAnyTags([$meta['name']], 'category');
        } elseif ($this->type === 'languages') {
            $query->where('primary_language', $meta['name']);
        } else {
            $query->where('license_type', $meta['name']);
        }

        $alternatives = $query->orderByDesc('overall_health_score')->paginate($this->perPage);

        $heading = match ($this->type) {
            'categories' => $meta['name'].' open-source alternatives',
            'languages' => 'Open-source tools written in '.$meta['name'],
            'licenses' => 'Projects under the '.$meta['name'].' license',
            default => $meta['name'],
        };

        $description = Str::limit(
            $heading.'. Browse self-hostable alternatives with health scores and licenses.',
            155
        );

        return view('livewire.browse-hub-show', [
            'type' => $this->type,
            'meta' => $meta,
            'heading' => $heading,
            'alternatives' => $alternatives,
        ])->layout('layouts.app', [
            'title' => $heading.' | '.$seo->siteName(),
            'description' => $description,
            'canonical' => $this->canonicalForPage($seo),
            'robots' => $this->pageNumber() > 1 ? 'noindex,follow' : null,
            'jsonLd' => $this->buildHubSchema($heading, $description, $alternatives->getCollection()->map(fn ($alt) => ['name' => $alt->name, 'slug' => $alt->slug])->all(), $this->canonicalForPage($seo)),
        ]);
    }


    protected function pageNumber(): int
    {
        return max(1, (int) request()->query('page', 1));
    }

    protected function canonicalForPage(SeoManager $seo): string
    {
        $canonical = $seo->currentCanonicalUrl();

        return $this->pageNumber() > 1
            ? $canonical.'?page='.$this->pageNumber()
            : $canonical;
    }

    /**
     * @param array<int, array{name?: string, slug?: string, count?: int}> $items
     * @return list<array<string, mixed>>
     */
    protected function buildHubSchema(string $title, string $description, array $items, string $canonical): array
    {
        $base = rtrim(\App\Support\CanonicalUrl::base(request()), '/');
        $typeName = match ($this->type) {
            'categories' => 'Categories',
            'languages' => 'Programming languages',
            'licenses' => 'Open-source licenses',
            default => 'Browse',
        };

        $elements = [];
        foreach (array_values($items) as $index => $item) {
            $slug = $item['slug'] ?? null;
            if (! $slug) {
                continue;
            }
            $url = $this->slug === null
                ? $base.'/browse/'.$this->type.'/'.$slug
                : $base.'/alternatives/'.$slug;

            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'] ?? '',
                'url' => $url,
            ];
        }

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base.'/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $typeName, 'item' => $base.'/browse/'.$this->type],
            ],
        ];

        if ($this->slug !== null) {
            $breadcrumb['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $title,
                'item' => $canonical,
            ];
        }

        return [[
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => $this->slug === null ? 'CollectionPage' : 'CollectionPage',
                    '@id' => $canonical.'#webpage',
                    'url' => $canonical,
                    'name' => $title,
                    'description' => $description,
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => app(SeoManager::class)->siteName(),
                        'url' => $base.'/',
                    ],
                    'mainEntity' => ['@id' => $canonical.'#itemlist'],
                    'breadcrumb' => ['@id' => $canonical.'#breadcrumb'],
                ],
                [
                    '@type' => 'ItemList',
                    '@id' => $canonical.'#itemlist',
                    'name' => $this->slug === null ? $typeName : $title,
                    'numberOfItems' => count($elements),
                    'itemListElement' => $elements,
                ],
                array_merge($breadcrumb, ['@id' => $canonical.'#breadcrumb']),
            ],
        ]];
    }

    /** @return list<array{name: string, slug: string, count: int}> */
    protected function categoryIndexItems(): array
    {
        $names = OssCategory::activeNames();
        if ($names === []) {
            try {
                $names = Tag::query()->where('type', 'category')->orderBy('name')->pluck('name')->all();
            } catch (\Throwable) {
                $names = CategoryCatalog::names();
            }
        }

        $items = [];
        foreach ($names as $name) {
            $count = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->withAnyTags([$name], 'category')
                ->count();
            if ($count < 1) {
                continue;
            }
            $items[] = [
                'name' => $name,
                'slug' => Str::slug($name),
                'count' => $count,
            ];
        }

        usort($items, fn ($a, $b) => $b['count'] <=> $a['count']);

        return $items;
    }

    /** @return list<array{name: string, slug: string, count: int}> */
    protected function languageIndexItems(): array
    {
        $rows = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->whereNotNull('primary_language')
            ->where('primary_language', '!=', '')
            ->selectRaw('primary_language as name, COUNT(*) as aggregate')
            ->groupBy('primary_language')
            ->orderByDesc('aggregate')
            ->get();

        return $rows->map(fn ($r) => [
            'name' => $r->name,
            'slug' => Str::slug($r->name),
            'count' => (int) $r->aggregate,
        ])->all();
    }

    /** @return list<array{name: string, slug: string, count: int}> */
    protected function licenseIndexItems(): array
    {
        $preferred = LicenseType::activeNames();
        $rows = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->whereNotNull('license_type')
            ->where('license_type', '!=', '')
            ->selectRaw('license_type as name, COUNT(*) as aggregate')
            ->groupBy('license_type')
            ->orderByDesc('aggregate')
            ->get();

        $items = $rows->map(fn ($r) => [
            'name' => $r->name,
            'slug' => Str::slug($r->name),
            'count' => (int) $r->aggregate,
        ])->all();

        // Prefer known license types first when counts exist
        if ($preferred !== []) {
            usort($items, function ($a, $b) use ($preferred) {
                $ai = array_search($a['name'], $preferred, true);
                $bi = array_search($b['name'], $preferred, true);
                if ($ai === false && $bi === false) {
                    return $b['count'] <=> $a['count'];
                }
                if ($ai === false) {
                    return 1;
                }
                if ($bi === false) {
                    return -1;
                }

                return $ai <=> $bi;
            });
        }

        return $items;
    }

    /** @return array{name: string, slug: string}|null */
    protected function resolveShowMeta(): ?array
    {
        $slug = $this->slug;
        if (! $slug) {
            return null;
        }

        $items = match ($this->type) {
            'categories' => $this->categoryIndexItems(),
            'languages' => $this->languageIndexItems(),
            'licenses' => $this->licenseIndexItems(),
            default => [],
        };

        foreach ($items as $item) {
            if ($item['slug'] === $slug || Str::slug($item['name']) === $slug) {
                return $item;
            }
        }

        // Fallback: match language/license by case-insensitive name from slug
        $guess = Str::of($slug)->replace('-', ' ')->title()->toString();
        if ($this->type === 'languages') {
            $found = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->whereRaw('LOWER(primary_language) = ?', [Str::lower($guess)])
                ->value('primary_language');
            if ($found) {
                return ['name' => $found, 'slug' => Str::slug($found)];
            }
            // try exact slug match against stored values
            foreach ($this->languageIndexItems() as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        }

        if ($this->type === 'licenses') {
            foreach ($this->licenseIndexItems() as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        }

        if ($this->type === 'categories') {
            foreach ($this->categoryIndexItems() as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        }

        return null;
    }
}
