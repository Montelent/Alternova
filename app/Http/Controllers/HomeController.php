<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $hasSponsored = Schema::hasColumn('open_source_alternatives', 'is_sponsored');

        $featuredQuery = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true);

        if ($hasSponsored) {
            $featuredQuery
                ->orderByRaw('CASE WHEN is_sponsored = 1 AND (sponsored_until IS NULL OR sponsored_until > ?) THEN 0 ELSE 1 END', [now()])
                ->orderByDesc('is_featured')
                ->orderByDesc('overall_health_score');
        } else {
            $featuredQuery
                ->where('is_featured', true)
                ->orderByDesc('overall_health_score');
        }

        $featured = $featuredQuery->limit(6)->get();

        if ($featured->isEmpty() || (! $hasSponsored && $featured->where('is_featured', true)->isEmpty())) {
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

        $collections = collect();
        try {
            if (Schema::hasTable('collections')) {
                $collections = Collection::query()
                    ->where('is_published', true)
                    ->where('is_featured', true)
                    ->withCount('items')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->limit(6)
                    ->get();

                if ($collections->isEmpty()) {
                    $collections = Collection::query()
                        ->where('is_published', true)
                        ->withCount('items')
                        ->orderBy('sort_order')
                        ->orderByDesc('updated_at')
                        ->limit(4)
                        ->get();
                }
            }
        } catch (\Throwable) {
        }

        $stats = [
            'alternatives' => OpenSourceAlternative::query()->where('is_published', true)->count(),
            'tools' => ProprietaryTool::query()->where('is_published', true)->count(),
            'featured' => OpenSourceAlternative::query()->where('is_published', true)->where('is_featured', true)->count(),
        ];

        return view('welcome', compact('featured', 'recent', 'popular', 'tools', 'collections', 'stats'));
    }
}
