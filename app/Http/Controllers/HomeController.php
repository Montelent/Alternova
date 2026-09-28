<?php

namespace App\Http\Controllers;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
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

        if ($featured->isEmpty()) {
            $featured = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->orderByDesc('overall_health_score')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get();
        }

        $recent = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $popular = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true)
            ->where('votes_count', '>', 0)
            ->orderByDesc('votes_count')
            ->limit(6)
            ->get();

        $tools = ProprietaryTool::query()
            ->where('is_published', true)
            ->withCount(['publishedAlternatives as alternatives_count'])
            ->having('alternatives_count', '>', 0)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $stats = [
            'alternatives' => OpenSourceAlternative::query()->where('is_published', true)->count(),
            'tools' => ProprietaryTool::query()->where('is_published', true)->count(),
            'featured' => OpenSourceAlternative::query()->where('is_published', true)->where('is_featured', true)->count(),
        ];

        return view('welcome', compact('featured', 'recent', 'popular', 'tools', 'stats'));
    }
}
