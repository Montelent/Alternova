<?php

namespace App\Http\Controllers\Api;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SearchSuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $limit = (int) ($validated['limit'] ?? 8);
        $term = str_replace(['%', '_'], ['\\%', '\\_'], $q);

        try {
            $tools = ProprietaryTool::query()
                ->where('is_published', true)
                ->where('name', 'like', '%'.$term.'%')
                ->orderBy('name')
                ->limit($limit)
                ->get(['id', 'name', 'slug']);

            $alternatives = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->where('name', 'like', '%'.$term.'%')
                ->orderBy('name')
                ->limit($limit)
                ->get(['id', 'name', 'slug']);

            $data = $tools->map(fn ($t) => [
                'type' => 'tool',
                'name' => $t->name,
                'slug' => $t->slug,
                'url' => route('alternativesto.show', $t->slug),
            ])->concat($alternatives->map(fn ($a) => [
                'type' => 'alternative',
                'name' => $a->name,
                'slug' => $a->slug,
                'url' => route('alternatives.show', $a->slug),
            ]))->values()->all();

            return response()->json(['data' => array_slice($data, 0, $limit)]);
        } catch (\Throwable $e) {
            return response()->json(['data' => []]);
        }
    }
}
