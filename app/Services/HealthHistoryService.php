<?php

namespace App\Services;

use App\Models\HealthScoreSnapshot;
use App\Models\OpenSourceAlternative;
use App\Models\RepoMetric;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class HealthHistoryService
{
    public function tableReady(): bool
    {
        try {
            return Schema::hasTable('health_score_snapshots');
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Weighted 0–100 score from GitHub activity. Reads metrics from DB (not a stale relation).
     */
    public function recalculate(OpenSourceAlternative $alt): float
    {
        $metric = RepoMetric::query()
            ->where('open_source_alternative_id', $alt->id)
            ->first();

        if (! $metric) {
            $alt->forceFill(['overall_health_score' => 0])->saveQuietly();

            return 0.0;
        }

        $stars = max((int) $metric->github_stars, 0);
        $forks = max((int) $metric->github_forks, 0);
        $issues = max((int) $metric->open_issues, 0);

        // log10 scale so huge repos don't max everything instantly
        $starsScore = min(log10(max($stars, 1)) * 15, 40);
        $forksScore = min(log10(max($forks, 1)) * 10, 20);
        $issuesScore = $issues < 50 ? 15 : max(0, 15 - ($issues / 20));

        $recencyScore = 0;
        if ($metric->last_commit_at) {
            if ($metric->last_commit_at->gt(now()->subMonths(3))) {
                $recencyScore = 25;
            } elseif ($metric->last_commit_at->gt(now()->subYear())) {
                $recencyScore = 10;
            }
        }

        $score = round(min($starsScore + $forksScore + $issuesScore + $recencyScore, 100), 2);

        $alt->forceFill(['overall_health_score' => $score])->saveQuietly();

        // Keep history chart in sync (no-op if snapshots table missing)
        try {
            $alt->setRelation('repoMetric', $metric);
            $this->record($alt, $score);
        } catch (\Throwable) {
        }

        return $score;
    }

    public function record(OpenSourceAlternative $alt, ?float $score = null): ?HealthScoreSnapshot
    {
        if (! $this->tableReady()) {
            return null;
        }

        $score = $score ?? (float) $alt->overall_health_score;
        $metric = $alt->relationLoaded('repoMetric')
            ? $alt->repoMetric
            : RepoMetric::query()->where('open_source_alternative_id', $alt->id)->first();

        // Avoid duplicate snapshots within 6 hours with same score
        $recent = HealthScoreSnapshot::query()
            ->where('open_source_alternative_id', $alt->id)
            ->where('recorded_at', '>=', now()->subHours(6))
            ->orderByDesc('recorded_at')
            ->first();

        if ($recent && abs((float) $recent->score - $score) < 0.01) {
            return $recent;
        }

        return HealthScoreSnapshot::create([
            'open_source_alternative_id' => $alt->id,
            'score' => round($score, 2),
            'github_stars' => $metric?->github_stars,
            'github_forks' => $metric?->github_forks,
            'open_issues' => $metric?->open_issues,
            'recorded_at' => now(),
        ]);
    }

    /**
     * @return Collection<int, array{score: float, recorded_at: string, stars: ?int}>
     */
    public function series(OpenSourceAlternative $alt, int $limit = 30): Collection
    {
        if (! $this->tableReady()) {
            return collect();
        }

        $rows = HealthScoreSnapshot::query()
            ->where('open_source_alternative_id', $alt->id)
            ->orderByDesc('recorded_at')
            ->limit($limit)
            ->get()
            ->sortBy('recorded_at')
            ->values();

        if ($rows->isEmpty() && (float) $alt->overall_health_score > 0) {
            // Seed a single point so charts are not empty
            $this->record($alt);
            $rows = HealthScoreSnapshot::query()
                ->where('open_source_alternative_id', $alt->id)
                ->orderBy('recorded_at')
                ->get();
        }

        return $rows->map(fn (HealthScoreSnapshot $s) => [
            'score' => (float) $s->score,
            'recorded_at' => $s->recorded_at?->toIso8601String(),
            'label' => $s->recorded_at?->format('M j') ?? '',
            'stars' => $s->github_stars,
        ]);
    }

    /**
     * SVG polyline points for a sparkline (viewBox 0 0 100 28).
     */
    public function sparklinePoints(Collection $series): string
    {
        $scores = $series->pluck('score')->map(fn ($s) => (float) $s)->values();
        if ($scores->isEmpty()) {
            return '';
        }

        if ($scores->count() === 1) {
            $y = 28 - ($scores[0] / 100 * 24) - 2;

            return "0,{$y} 100,{$y}";
        }

        $min = max(0, $scores->min() - 5);
        $max = min(100, $scores->max() + 5);
        if ($max <= $min) {
            $max = $min + 1;
        }

        $n = $scores->count();
        $pts = [];
        foreach ($scores as $i => $score) {
            $x = ($i / ($n - 1)) * 100;
            $y = 28 - (($score - $min) / ($max - $min) * 24) - 2;
            $pts[] = round($x, 2).','.round($y, 2);
        }

        return implode(' ', $pts);
    }

    public function trend(Collection $series): string
    {
        if ($series->count() < 2) {
            return 'flat';
        }

        $first = (float) $series->first()['score'];
        $last = (float) $series->last()['score'];
        $delta = $last - $first;

        if ($delta > 1) {
            return 'up';
        }
        if ($delta < -1) {
            return 'down';
        }

        return 'flat';
    }
}
