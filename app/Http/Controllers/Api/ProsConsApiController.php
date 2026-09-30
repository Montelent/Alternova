<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OpenSourceAlternative;
use App\Services\ProsConsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProsConsApiController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $alt = OpenSourceAlternative::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $service = app(ProsConsService::class);
        if (! $service->ready()) {
            return response()->json([
                'data' => ['pros' => [], 'cons' => []],
                'meta' => ['ready' => false],
            ]);
        }

        $lists = $service->listsFor($alt);

        $map = fn ($item) => [
            'id' => $item->id,
            'type' => $item->type,
            'body' => $item->body,
            'votes' => (int) $item->votes_count,
            'featured' => (bool) $item->is_featured,
            'author' => $item->author_name,
            'created_at' => optional($item->created_at)?->toIso8601String(),
        ];

        return response()->json([
            'data' => [
                'alternative' => [
                    'name' => $alt->name,
                    'slug' => $alt->slug,
                ],
                'pros' => $lists['pros']->map($map)->values(),
                'cons' => $lists['cons']->map($map)->values(),
            ],
            'meta' => ['ready' => true],
        ]);
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $alt = OpenSourceAlternative::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $validated = $request->validate([
            'type' => 'required|in:pro,con',
            'body' => 'required|string|min:8|max:280',
            'author_name' => 'nullable|string|max:80',
        ]);

        $result = app(ProsConsService::class)->submit(
            $alt,
            $validated['type'],
            $validated['body'],
            $validated['author_name'] ?? null,
            null,
            $request->ip(),
            $request->user()
        );

        return response()->json([
            'ok' => $result['ok'],
            'message' => $result['message'],
            'data' => $result['item'] ? [
                'id' => $result['item']->id,
                'type' => $result['item']->type,
                'body' => $result['item']->body,
                'approved' => (bool) $result['item']->is_approved,
            ] : null,
        ], $result['ok'] ? 201 : 422);
    }
}
