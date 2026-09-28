<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featured = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderByDesc('overall_health_score')
            ->limit(6)
            ->get();

        // Fallback: top published by health if nothing is featured yet
        if ($featured->isEmpty()) {
            $featured = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->orderByDesc('overall_health_score')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get();
        }

        $stats = [
            'alternatives' => OpenSourceAlternative::query()->where('is_published', true)->count(),
            'featured' => OpenSourceAlternative::query()->where('is_published', true)->where('is_featured', true)->count(),
        ];

        return view('welcome', [
            'featured' => $featured,
            'stats' => $stats,
        ]);
    }
}
