<?php

namespace App\Services;

use App\Models\SavedDomain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SavedDomainService
{
    public function sessionKey(): string
    {
        return substr(hash('sha256', session()->getId() ?: request()->ip()), 0, 64);
    }

    public function toggle(string $domain, ?string $status = null): array
    {
        if (! Schema::hasTable('saved_domains')) {
            return ['saved' => false, 'message' => 'Run migrations to enable saved domains'];
        }

        $domain = strtolower(trim($domain));
        $q = SavedDomain::query()->where('domain', $domain);

        if (Auth::id()) {
            $q->where('user_id', Auth::id());
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        $existing = $q->first();
        if ($existing) {
            $existing->delete();

            return ['saved' => false, 'message' => 'Removed from saved domains'];
        }

        SavedDomain::create([
            'domain' => $domain,
            'status' => $status,
            'user_id' => Auth::id(),
            'session_id' => $this->sessionKey(),
        ]);

        return ['saved' => true, 'message' => Auth::check() ? 'Saved to your account' : 'Saved on this device'];
    }

    public function list(): Collection
    {
        if (! Schema::hasTable('saved_domains')) {
            return collect();
        }

        $q = SavedDomain::query();

        if (Auth::id()) {
            $q->where(function ($inner) {
                $inner->where('user_id', Auth::id())
                    ->orWhere('session_id', $this->sessionKey());
            });
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        return $q->orderByDesc('created_at')->limit(100)->get();
    }

    public function mergeSessionIntoUser(int $userId): int
    {
        if (! Schema::hasTable('saved_domains')) {
            return 0;
        }

        $sid = $this->sessionKey();
        $rows = SavedDomain::query()
            ->where('session_id', $sid)
            ->whereNull('user_id')
            ->get();

        $n = 0;
        foreach ($rows as $row) {
            $exists = SavedDomain::query()
                ->where('user_id', $userId)
                ->where('domain', $row->domain)
                ->exists();
            if ($exists) {
                $row->delete();
            } else {
                $row->update(['user_id' => $userId]);
                $n++;
            }
        }

        return $n;
    }
}
