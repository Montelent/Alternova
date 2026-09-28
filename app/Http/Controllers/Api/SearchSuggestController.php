<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $like = '%'.$q.'%';

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

        return response()->json([
            'data' => $tools->concat($alternatives)->values(),
        ]);
    }
}
