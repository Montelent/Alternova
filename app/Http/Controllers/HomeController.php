<?php

namespace App\Http\Controllers;

use App\Models\AlternativeVote;
use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $hasSponsored = Schema::hasColumn('open_source_alternatives', 'is_sponsored');
        $with = ['proprietaryTool', 'repoMetric'];
        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $with[] = 'proprietaryTools';
            }
        } catch (\Throwable) {
        }

        $featuredQuery = OpenSourceAlternative::query()
            ->with($with)
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
                ->with($with)
                ->where('is_published', true)
                ->orderByDesc('overall_health_score')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get();
        }

        $recent = OpenSourceAlternative::query()
            ->with($with)
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $popular = OpenSourceAlternative::query()
            ->with($with)
            ->where('is_published', true)
            ->where('votes_count', '>', 0)
            ->orderByDesc('votes_count')
            ->limit(6)
            ->get();

        $trending = $this->trending(6, $with);

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
                    ->withCount('items')
                    ->orderByDesc('updated_at')
                    ->limit(6)
                    ->get();
            }
        } catch (\Throwable) {
        }

        $stats = [
            'alternatives' => OpenSourceAlternative::query()->where('is_published', true)->count(),
            'tools' => ProprietaryTool::query()->where('is_published', true)->count(),
        ];

        return view('welcome', compact(
            'featured',
            'recent',
            'popular',
            'trending',
            'tools',
            'collections',
            'stats'
        ));
    }

    protected function trending(int $limit = 6, array $with = ['proprietaryTool', 'repoMetric'])
    {
        try {
            if (! Schema::hasTable('alternative_votes')) {
                return collect();
            }

            $ids = AlternativeVote::query()
                ->select('open_source_alternative_id', DB::raw('COUNT(*) as c'))
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy('open_source_alternative_id')
                ->orderByDesc('c')
                ->limit($limit)
                ->pluck('open_source_alternative_id');

            if ($ids->isEmpty()) {
                return OpenSourceAlternative::query()
                    ->with($with)
                    ->where('is_published', true)
                    ->orderByDesc('votes_count')
                    ->limit($limit)
                    ->get();
            }

            return OpenSourceAlternative::query()
                ->with($with)
                ->where('is_published', true)
                ->whereIn('id', $ids)
                ->orderByRaw('FIELD(id, '.$ids->implode(',').')')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }
}
