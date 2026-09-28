<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use Illuminate\View\View;

class WhatsNewController extends Controller
{
    public function __invoke(): View
    {
        $weeks = 8;

        $items = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('created_at', '>=', now()->subWeeks($weeks))
            ->orderByDesc('created_at')
            ->limit(60)
            ->get()
            ->groupBy(fn ($alt) => $alt->created_at->startOfWeek()->toDateString());

        // If nothing in the window, show latest published anyway
        if ($items->isEmpty()) {
            $latest = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();

            $items = $latest->groupBy(fn ($alt) => $alt->created_at?->startOfWeek()->toDateString() ?? 'recent');
        }

        return view('pages.whats-new', [
            'groups' => $items,
            'title' => "What's new — Alternova",
            'description' => 'Recently published open-source alternatives on Alternova, grouped by week.',
        ]);
    }
}
