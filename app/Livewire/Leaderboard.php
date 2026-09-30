<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Leaderboard extends Component
{
    public string $tab = 'health';

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['health', 'votes', 'stars', 'rising'], true)) {
            $this->tab = $tab;
        }
    }

    public function render()
    {
        $base = OpenSourceAlternative::query()
            ->with(['proprietaryTool', 'repoMetric'])
            ->where('is_published', true);

        $rows = match ($this->tab) {
            'votes' => (clone $base)->orderByDesc('votes_count')->orderByDesc('overall_health_score')->limit(25)->get(),
            'stars' => (clone $base)
                ->leftJoin('repo_metrics', 'repo_metrics.open_source_alternative_id', '=', 'open_source_alternatives.id')
                ->orderByDesc('repo_metrics.github_stars')
                ->select('open_source_alternatives.*')
                ->limit(25)
                ->get(),
            'rising' => $this->rising($base),
            default => (clone $base)->orderByDesc('overall_health_score')->orderByDesc('votes_count')->limit(25)->get(),
        };

        return view('livewire.leaderboard', [
            'rows' => $rows,
        ])->layout('layouts.app', [
            'title' => 'Open-source leaderboards | Alternova',
            'description' => 'Rankings of the healthiest, most-voted, starriest, and rising open-source alternatives on Alternova.',
            'canonical' => route('leaderboard'),
        ]);
    }

    protected function rising($base)
    {
        try {
            if (! Schema::hasTable('health_score_snapshots')) {
                return (clone $base)->orderByDesc('created_at')->limit(25)->get();
            }

            // Alternatives with the largest positive health delta between oldest and newest snapshot in 14 days
            $ids = DB::table('health_score_snapshots as newer')
                ->join('health_score_snapshots as older', function ($join) {
                    $join->on('newer.open_source_alternative_id', '=', 'older.open_source_alternative_id')
                        ->whereRaw('older.recorded_at = (
                            SELECT MIN(s.recorded_at) FROM health_score_snapshots s
                            WHERE s.open_source_alternative_id = newer.open_source_alternative_id
                            AND s.recorded_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                        )');
                })
                ->where('newer.recorded_at', '>=', now()->subDays(14))
                ->whereRaw('newer.recorded_at = (
                    SELECT MAX(s2.recorded_at) FROM health_score_snapshots s2
                    WHERE s2.open_source_alternative_id = newer.open_source_alternative_id
                )')
                ->select('newer.open_source_alternative_id')
                ->selectRaw('(newer.score - older.score) as delta')
                ->orderByDesc('delta')
                ->limit(25)
                ->pluck('open_source_alternative_id');

            if ($ids->isEmpty()) {
                return (clone $base)->orderByDesc('created_at')->limit(25)->get();
            }

            $alts = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            return $ids->map(fn ($id) => $alts->get($id))->filter()->values();
        } catch (\Throwable) {
            return (clone $base)->orderByDesc('created_at')->limit(25)->get();
        }
    }
}
