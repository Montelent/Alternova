<?php

namespace App\Services;

use App\Models\FavoriteAlternative;
use App\Models\OpenSourceAlternative;
use Illuminate\Support\Collection;

class FavoriteService
{
    public function sessionKey(): string
    {
        return substr(hash('sha256', session()->getId() ?: request()->ip()), 0, 64);
    }

    public function toggle(OpenSourceAlternative $alternative): array
    {
        $sid = $this->sessionKey();

        $existing = FavoriteAlternative::query()
            ->where('session_id', $sid)
            ->where('open_source_alternative_id', $alternative->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return ['favorited' => false, 'message' => 'Removed from favorites'];
        }

        FavoriteAlternative::create([
            'session_id' => $sid,
            'open_source_alternative_id' => $alternative->id,
        ]);

        return ['favorited' => true, 'message' => 'Saved to favorites'];
    }

    public function has(OpenSourceAlternative $alternative): bool
    {
        return FavoriteAlternative::query()
            ->where('session_id', $this->sessionKey())
            ->where('open_source_alternative_id', $alternative->id)
            ->exists();
    }

    public function list(): Collection
    {
        return FavoriteAlternative::query()
            ->with(['alternative.proprietaryTool', 'alternative.repoMetric'])
            ->where('session_id', $this->sessionKey())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->pluck('alternative')
            ->filter();
    }

    public function clear(): void
    {
        FavoriteAlternative::query()->where('session_id', $this->sessionKey())->delete();
    }
}
