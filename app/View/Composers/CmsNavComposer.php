<?php

namespace App\View\Composers;

use App\Models\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CmsNavComposer
{
    public function compose(View $view): void
    {
        $footerPages = collect();
        $navPages = collect();

        try {
            if (Schema::hasTable('pages')) {
                $footerPages = Page::query()
                    ->published()
                    ->where('show_in_footer', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'title', 'slug']);

                $navPages = Page::query()
                    ->published()
                    ->where('show_in_nav', true)
                    ->orderBy('sort_order')
                    ->orderBy('title')
                    ->get(['id', 'title', 'slug']);
            }
        } catch (\Throwable) {
        }

        $view->with(compact('footerPages', 'navPages'));
    }
}
