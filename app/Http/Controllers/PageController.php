<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

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

        return view('pages.cms', [
            'page' => $page,
            'title' => $page->seoTitle(),
            'description' => $page->seoDescription(),
            'robots' => $page->robots_meta,
        ]);
    }

    protected function renderReserved(string $slug, array $meta): View
    {
        if ($this->pagesReady()) {
            $page = Page::query()->published()->where('slug', $slug)->first();
            if ($page) {
                return view('pages.cms', [
                    'page' => $page,
                    'title' => $page->seoTitle() ?: $meta['title'],
                    'description' => $page->seoDescription() ?: $meta['description'],
                    'robots' => $page->robots_meta,
                ]);
            }
        }

        return view($meta['fallback'], [
            'title' => $meta['title'],
            'description' => $meta['description'],
        ]);
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
