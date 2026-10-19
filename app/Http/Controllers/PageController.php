<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function about(): View
    {
        return $this->renderReserved('about', [
            'title' => 'About Alternova',
            'description' => 'Learn about Alternova — a free directory of open-source software alternatives and a brandable domain name idea generator.',
            'fallback' => 'pages.about',
        ]);
    }

    public function privacy(): View
    {
        return $this->renderReserved('privacy', [
            'title' => 'Privacy Policy — Alternova',
            'description' => 'Privacy policy for Alternova: how we handle data, cookies, and third-party services including advertising.',
            'fallback' => 'pages.privacy',
        ]);
    }

    public function terms(): View
    {
        return $this->renderReserved('terms', [
            'title' => 'Terms of Use — Alternova',
            'description' => 'Terms of use for the Alternova website and tools.',
            'fallback' => 'pages.terms',
        ]);
    }

    public function disclosure(): View
    {
        return $this->renderReserved('disclosure', [
            'title' => 'Affiliate & Advertising Disclosure — Alternova',
            'description' => 'How Alternova uses affiliate links and advertising, and how that relates to open-source listings.',
            'fallback' => 'pages.disclosure',
        ]);
    }

    public function show(string $slug): View
    {
        abort_unless($this->pagesReady(), 404);

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $title = $page->seoTitle();
        $description = $page->seoDescription();
        $canonical = $page->publicUrl();

        return view('pages.cms', [
            'page' => $page,
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $page->robots_meta,
            'jsonLd' => $this->pageJsonLd($page, $title, $description, $canonical),
        ]);
    }

    protected function renderReserved(string $slug, array $meta): View
    {
        if ($this->pagesReady()) {
            $page = Page::query()->published()->where('slug', $slug)->first();
            if ($page) {
                $title = $page->seoTitle() ?: $meta['title'];
                $description = $page->seoDescription() ?: $meta['description'];
                $canonical = $page->publicUrl();

                return view('pages.cms', [
                    'page' => $page,
                    'title' => $title,
                    'description' => $description,
                    'canonical' => $canonical,
                    'robots' => $page->robots_meta,
                    'jsonLd' => $this->pageJsonLd($page, $title, $description, $canonical),
                ]);
            }
        }

        return view($meta['fallback'], [
            'title' => $meta['title'],
            'description' => $meta['description'],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function pageJsonLd(Page $page, string $title, string $description, string $canonical): array
    {
        $site = rtrim((string) config('app.url'), '/');
        $siteName = config('app.name', 'Alternova');
        try {
            $siteName = app(SeoManager::class)->siteName();
        } catch (\Throwable) {
        }

        $webPage = [
            '@type' => 'WebPage',
            '@id' => $canonical.'#webpage',
            'url' => $canonical,
            'name' => $title,
            'description' => Str::limit(strip_tags($description), 300),
            'isPartOf' => [
                '@type' => 'WebSite',
                '@id' => $site.'/#website',
                'name' => $siteName,
                'url' => $site,
            ],
            'breadcrumb' => ['@id' => $canonical.'#breadcrumb'],
        ];

        if (filled($page->body_html)) {
            $webPage['mainEntity'] = [
                '@type' => 'Article',
                'headline' => $page->title,
                'description' => Str::limit(strip_tags((string) ($page->excerpt ?: $description)), 200),
                'dateModified' => optional($page->updated_at)?->toAtomString(),
                'datePublished' => optional($page->published_at ?? $page->created_at)?->toAtomString(),
                'author' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                    'url' => $site,
                ],
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
                    'name' => $page->title,
                    'item' => $canonical,
                ],
            ],
        ];

        return [
            [
                '@context' => 'https://schema.org',
                '@graph' => [$webPage, $breadcrumb],
            ],
        ];
    }

    protected function pagesReady(): bool
    {
        try {
            return Schema::hasTable('pages');
        } catch (\Throwable) {
            return false;
        }
    }
}
