<?php

namespace App\Services;

use App\Models\FavoriteAlternative;
use App\Models\OpenSourceAlternative;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class FavoriteService
{
    public function sessionKey(): string
    {
        return substr(hash('sha256', session()->getId() ?: request()->ip()), 0, 64);
    }

    public function userId(): ?int
    {
        return Auth::id();
    }

    protected function hasUserColumn(): bool
    {
        try {
            return Schema::hasColumn('favorite_alternatives', 'user_id');
        } catch (\Throwable) {
            return false;
        }
    }

    public function toggle(OpenSourceAlternative $alternative): array
    {
        $existing = $this->findExisting($alternative);

        if ($existing) {
            $existing->delete();

            return ['favorited' => false, 'message' => 'Removed from favorites'];
        }

        $data = [
            'open_source_alternative_id' => $alternative->id,
            'session_id' => $this->sessionKey(),
        ];

        if ($this->hasUserColumn() && $this->userId()) {
            $data['user_id'] = $this->userId();
        }

        FavoriteAlternative::create($data);

        return ['favorited' => true, 'message' => Auth::check() ? 'Saved to your account' : 'Saved on this device — sign in to sync'];
    }

    public function has(OpenSourceAlternative $alternative): bool
    {
        return $this->findExisting($alternative) !== null;
    }

    protected function findExisting(OpenSourceAlternative $alternative): ?FavoriteAlternative
    {
        $q = FavoriteAlternative::query()
            ->where('open_source_alternative_id', $alternative->id);

        if ($this->hasUserColumn() && $this->userId()) {
            $q->where(function ($inner) {
                $inner->where('user_id', $this->userId())
                    ->orWhere('session_id', $this->sessionKey());
            });
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        return $q->first();
    }

    public function list(): Collection
    {
        $q = FavoriteAlternative::query()
            ->with(['alternative.proprietaryTool', 'alternative.repoMetric']);

        if ($this->hasUserColumn() && $this->userId()) {
            $q->where(function ($inner) {
                $inner->where('user_id', $this->userId())
                    ->orWhere('session_id', $this->sessionKey());
            });
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        return $q->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->pluck('alternative')
            ->filter()
            ->unique('id')
            ->values();
    }

    public function clear(): void
    {
        $q = FavoriteAlternative::query();

        if ($this->hasUserColumn() && $this->userId()) {
            $q->where(function ($inner) {
                $inner->where('user_id', $this->userId())
                    ->orWhere('session_id', $this->sessionKey());
            });
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        $q->delete();
    }

    /**
     * After login/register: attach session favorites to the user account.
     */
    public function mergeSessionIntoUser(User $user): int
    {
        if (! $this->hasUserColumn()) {
            return 0;
        }

        $sid = $this->sessionKey();
        $rows = FavoriteAlternative::query()
            ->where('session_id', $sid)
            ->where(function ($q) {
                $q->whereNull('user_id')->orWhere('user_id', 0);
            })
            ->get();

        $merged = 0;
        foreach ($rows as $row) {
            $exists = FavoriteAlternative::query()
                ->where('user_id', $user->id)
                ->where('open_source_alternative_id', $row->open_source_alternative_id)
                ->exists();

            if ($exists) {
                $row->delete();
            } else {
                $row->update(['user_id' => $user->id]);
                $merged++;
            }
        }

        return $merged;
    }
}
