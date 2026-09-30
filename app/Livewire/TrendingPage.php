<?php

namespace App\Livewire;

use App\Models\AlternativeVote;
use App\Models\OpenSourceAlternative;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class TrendingPage extends Component
{
    public int $days = 7;

    public function setDays(int $days): void
    {
        if (in_array($days, [7, 14, 30], true)) {
            $this->days = $days;
        }
    }

    public function render()
    {
        $rows = $this->trending();

        return view('livewire.trending-page', [
            'rows' => $rows,
        ])->layout('layouts.app', [
            'title' => 'Trending open-source alternatives | Alternova',
            'description' => 'Most upvoted open-source alternatives in the last '.$this->days.' days on Alternova.',
            'canonical' => route('trending'),
        ]);
    }

    protected function trending()
    {
        try {
            if (! Schema::hasTable('alternative_votes')) {
                return OpenSourceAlternative::query()
                    ->with(['proprietaryTool', 'repoMetric'])
                    ->where('is_published', true)
                    ->orderByDesc('votes_count')
                    ->limit(25)
                    ->get()
                    ->map(function ($alt) {
                        $alt->period_votes = (int) $alt->votes_count;

                        return $alt;
                    });
            }

            $since = now()->subDays($this->days);

            $counts = AlternativeVote::query()
                ->where('created_at', '>=', $since)
                ->select('open_source_alternative_id', DB::raw('COUNT(*) as period_votes'))
                ->groupBy('open_source_alternative_id')
                ->orderByDesc('period_votes')
                ->limit(25)
                ->pluck('period_votes', 'open_source_alternative_id');

            if ($counts->isEmpty()) {
                return collect();
            }

            $alts = OpenSourceAlternative::query()
                ->with(['proprietaryTool', 'repoMetric'])
                ->where('is_published', true)
                ->whereIn('id', $counts->keys())
                ->get()
                ->keyBy('id');

            return $counts->map(function ($votes, $id) use ($alts) {
                $alt = $alts->get($id);
                if (! $alt) {
                    return null;
                }
                $alt->period_votes = (int) $votes;

                return $alt;
            })->filter()->values();
        } catch (\Throwable) {
            return collect();
        }
    }
}
