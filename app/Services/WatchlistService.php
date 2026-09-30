<?php

namespace App\Services;

use App\Mail\HealthDropAlertMail;
use App\Models\OpenSourceAlternative;
use App\Models\User;
use App\Models\WatchedAlternative;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class WatchlistService
{
    public function ready(): bool
    {
        try {
            return Schema::hasTable('watched_alternatives');
        } catch (\Throwable) {
            return false;
        }
    }

    public function isWatching(OpenSourceAlternative $alt, ?User $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (! $user || ! $this->ready()) {
            return false;
        }

        return WatchedAlternative::query()
            ->where('user_id', $user->id)
            ->where('open_source_alternative_id', $alt->id)
            ->exists();
    }

    public function toggle(OpenSourceAlternative $alt, ?User $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (! $user || ! $this->ready()) {
            return false;
        }

        $existing = WatchedAlternative::query()
            ->where('user_id', $user->id)
            ->where('open_source_alternative_id', $alt->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        WatchedAlternative::create([
            'user_id' => $user->id,
            'open_source_alternative_id' => $alt->id,
            'notify_health_drop' => true,
            'last_notified_score' => $alt->overall_health_score,
        ]);

        return true;
    }

    /**
     * @return Collection<int, OpenSourceAlternative>
     */
    public function listFor(?User $user = null): Collection
    {
        $user = $user ?: Auth::user();
        if (! $user || ! $this->ready()) {
            return collect();
        }

        return OpenSourceAlternative::query()
            ->whereIn('id', WatchedAlternative::query()
                ->where('user_id', $user->id)
                ->pluck('open_source_alternative_id'))
            ->with(['proprietaryTool', 'repoMetric'])
            ->orderBy('name')
            ->get();
    }

    public function unwatch(int $alternativeId, ?User $user = null): void
    {
        $user = $user ?: Auth::user();
        if (! $user || ! $this->ready()) {
            return;
        }

        WatchedAlternative::query()
            ->where('user_id', $user->id)
            ->where('open_source_alternative_id', $alternativeId)
            ->delete();
    }

    public function notifyHealthDrop(OpenSourceAlternative $alt, float $previousScore, float $newScore, float $threshold = 5.0): void
    {
        if (! $this->ready()) {
            return;
        }

        $drop = $previousScore - $newScore;
        if ($drop < $threshold) {
            return;
        }

        $watches = WatchedAlternative::query()
            ->where('open_source_alternative_id', $alt->id)
            ->where('notify_health_drop', true)
            ->with('user')
            ->get();

        $url = url('/alternatives/'.$alt->slug);

        foreach ($watches as $watch) {
            $user = $watch->user;
            if (! $user) {
                continue;
            }

            if ($watch->last_notified_at && $watch->last_notified_at->gt(now()->subDays(7))
                && $watch->last_notified_score !== null
                && abs((float) $watch->last_notified_score - $newScore) < 1) {
                continue;
            }

            try {
                app(UserNotificationService::class)->create(
                    $user,
                    'health_drop',
                    $alt->name.' health dropped',
                    'Score moved from '.number_format($previousScore, 1).' to '.number_format($newScore, 1).' (−'.number_format($drop, 1).').',
                    $url,
                    [
                        'alternative_id' => $alt->id,
                        'previous' => $previousScore,
                        'new' => $newScore,
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning('In-app health notify failed: '.$e->getMessage());
            }

            if (! $user->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new HealthDropAlertMail(
                    userName: (string) $user->name,
                    alternative: $alt,
                    previousScore: $previousScore,
                    newScore: $newScore,
                ));

                $watch->forceFill([
                    'last_notified_score' => $newScore,
                    'last_notified_at' => now(),
                ])->save();
            } catch (\Throwable $e) {
                Log::warning('Health drop alert failed: '.$e->getMessage());
            }
        }
    }
}
