<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceAlternative;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlternativeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = OpenSourceAlternative::query()
            ->with(['proprietaryTool:id,name,slug', 'repoMetric', 'tags'])
            ->where('is_published', true);

        if ($q = $request->string('q')->trim()->toString()) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('primary_language', 'like', "%{$q}%");
            });
        }

        if ($license = $request->string('license')->trim()->toString()) {
            $query->where('license_type', $license);
        }

        if ($category = $request->string('category')->trim()->toString()) {
            $query->withAnyTags([$category], 'category');
        }

        $sort = $request->string('sort', 'health')->toString();
        $query = match ($sort) {
            'stars' => $query->leftJoin('repo_metrics', 'open_source_alternatives.id', '=', 'repo_metrics.open_source_alternative_id')
                ->orderByDesc('repo_metrics.github_stars')
                ->select('open_source_alternatives.*'),
            'votes' => $query->orderByDesc('votes_count'),
            'name' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderByDesc('overall_health_score'),
        };

        $perPage = min(50, max(1, (int) $request->input('per_page', 20)));
        $page = $query->paginate($perPage);

        return response()->json([
            'data' => $page->getCollection()->map(fn ($alt) => $this->transform($alt)),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'links' => [
                'self' => $page->url($page->currentPage()),
                'next' => $page->nextPageUrl(),
                'prev' => $page->previousPageUrl(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $alt = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric', 'tags'])
            ->where('is_published', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $this->transform($alt, detailed: true)]);
    }

    protected function transform(OpenSourceAlternative $alt, bool $detailed = false): array
    {
        $data = [
            'id' => $alt->id,
            'name' => $alt->name,
            'slug' => $alt->slug,
            'url' => route('alternatives.show', $alt),
            'description' => $alt->description,
            'license' => $alt->license_type,
            'language' => $alt->primary_language,
            'difficulty' => $alt->self_host_difficulty,
            'health_score' => (float) $alt->overall_health_score,
            'votes' => (int) ($alt->votes_count ?? 0),
            'repo_url' => $alt->repo_url,
            'website_url' => $alt->website_url,
            'categories' => $alt->tags->where('type', 'category')->pluck('name')->values(),
            'proprietary' => $alt->proprietaryTool ? [
                'name' => $alt->proprietaryTool->name,
                'slug' => $alt->proprietaryTool->slug,
            ] : null,
            'metrics' => $alt->repoMetric ? [
                'stars' => $alt->repoMetric->github_stars,
                'forks' => $alt->repoMetric->github_forks,
                'open_issues' => $alt->repoMetric->open_issues,
                'last_commit_at' => optional($alt->repoMetric->last_commit_at)?->toIso8601String(),
            ] : null,
            'featured' => (bool) $alt->is_featured,
            'updated_at' => optional($alt->updated_at)?->toIso8601String(),
        ];

        if ($detailed) {
            $data['pros'] = $alt->pros;
            $data['cons'] = $alt->cons;
            $data['docker_compose'] = $alt->docker_compose_blueprint;
            $data['editor_note'] = $alt->editor_note;
        }

        return $data;
    }
}
