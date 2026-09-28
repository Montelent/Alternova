<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function rss(): Response
    {
        $items = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $xml = view('feed.rss', [
            'items' => $items,
            'title' => config('app.name', 'Alternova').' — Open-source alternatives',
            'link' => url('/'),
            'description' => 'Latest self-hostable open-source alternatives on Alternova.',
            'updated' => optional($items->first()?->updated_at ?? now())->toAtomString(),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }

    public function atom(): Response
    {
        $items = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        $xml = view('feed.atom', [
            'items' => $items,
            'title' => config('app.name', 'Alternova').' — Open-source alternatives',
            'link' => url('/'),
            'feedUrl' => url('/feed/atom'),
            'description' => 'Latest self-hostable open-source alternatives on Alternova.',
            'updated' => optional($items->first()?->updated_at ?? now())->toAtomString(),
        ])->render();

        return response($xml, 200, [
            'Content-Type' => 'application/atom+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=1800',
        ]);
    }
}
