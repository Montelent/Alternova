<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Support\QueryCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchSuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        // Cap length to keep cache keys and LIKE scans reasonable
        $q = mb_substr($q, 0, 64);
        $cacheKey = 'suggest.v1.'.md5(mb_strtolower($q));

        $data = Cache::remember($cacheKey, QueryCache::SUGGEST_TTL, function () use ($q) {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';

            $alternatives = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->where(function ($builder) use ($like) {
                    $builder->where('name', 'like', $like)
                        ->orWhere('primary_language', 'like', $like);
                })
                ->orderByDesc('overall_health_score')
                ->limit(6)
                ->get(['name', 'slug', 'overall_health_score'])
                ->map(fn ($a) => [
                    'type' => 'alternative',
                    'name' => $a->name,
                    'url' => route('alternatives.show', $a->slug),
                    'meta' => 'Health '.number_format((float) $a->overall_health_score, 0),
                ]);

            $tools = ProprietaryTool::query()
                ->where('is_published', true)
                ->where('name', 'like', $like)
                ->orderBy('name')
                ->limit(4)
                ->get(['name', 'slug'])
                ->map(fn ($t) => [
                    'type' => 'tool',
                    'name' => $t->name,
                    'url' => route('tools.show', $t->slug),
                    'meta' => 'Proprietary',
                ]);

            return $tools->concat($alternatives)->values()->all();
        });

        return response()->json(['data' => $data])
            ->header('Cache-Control', 'public, max-age=60');
    }
}
