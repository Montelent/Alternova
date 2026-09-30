<?php

namespace App\Filament\Pages;

use App\Models\AlternativeComment;
use App\Models\AlternativeProsCon;
use App\Models\AlternativeVote;
use App\Models\DomainSearchLog;
use App\Models\OpenSourceAlternative;
use App\Models\UserNotification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EngagementAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Engagement';

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.engagement-analytics';

    public array $stats = [];

    public array $topVoted = [];

    public array $recentVotes = [];

    public array $prosConsBreakdown = [];

    public function mount(): void
    {
        $this->stats = [
            'votes_total' => $this->count('alternative_votes', fn () => AlternativeVote::query()->count()),
            'votes_7d' => $this->count('alternative_votes', fn () => AlternativeVote::query()->where('created_at', '>=', now()->subDays(7))->count()),
            'votes_30d' => $this->count('alternative_votes', fn () => AlternativeVote::query()->where('created_at', '>=', now()->subDays(30))->count()),
            'comments_total' => $this->count('alternative_comments', fn () => AlternativeComment::query()->count()),
            'comments_pending' => $this->count('alternative_comments', fn () => AlternativeComment::query()->where('is_approved', false)->where('is_hidden', false)->count()),
            'pros_cons_total' => $this->count('alternative_pros_cons', fn () => AlternativeProsCon::query()->count()),
            'pros_cons_pending' => $this->count('alternative_pros_cons', fn () => AlternativeProsCon::query()->where('is_approved', false)->count()),
            'pros_approved' => $this->count('alternative_pros_cons', fn () => AlternativeProsCon::query()->where('type', 'pro')->where('is_approved', true)->count()),
            'cons_approved' => $this->count('alternative_pros_cons', fn () => AlternativeProsCon::query()->where('type', 'con')->where('is_approved', true)->count()),
            'notifications_unread' => $this->count('user_notifications', fn () => UserNotification::query()->whereNull('read_at')->count()),
            'domain_searches_7d' => $this->count('domain_search_logs', fn () => DomainSearchLog::query()->where('created_at', '>=', now()->subDays(7))->count()),
        ];

        try {
            if (Schema::hasTable('open_source_alternatives')) {
                $this->topVoted = OpenSourceAlternative::query()
                    ->where('is_published', true)
                    ->where('votes_count', '>', 0)
                    ->orderByDesc('votes_count')
                    ->limit(10)
                    ->get(['name', 'slug', 'votes_count', 'overall_health_score'])
                    ->map(fn ($a) => [
                        'name' => $a->name,
                        'slug' => $a->slug,
                        'votes' => (int) $a->votes_count,
                        'health' => (float) $a->overall_health_score,
                        'url' => url('/alternatives/'.$a->slug),
                    ])
                    ->all();
            }
        } catch (\Throwable) {
            $this->topVoted = [];
        }

        try {
            if (Schema::hasTable('alternative_votes')) {
                $this->recentVotes = AlternativeVote::query()
                    ->with('alternative:id,name,slug')
                    ->orderByDesc('created_at')
                    ->limit(15)
                    ->get()
                    ->map(fn ($v) => [
                        'alt' => $v->alternative?->name ?? '—',
                        'slug' => $v->alternative?->slug,
                        'when' => optional($v->created_at)?->diffForHumans(),
                    ])
                    ->all();
            }
        } catch (\Throwable) {
            $this->recentVotes = [];
        }

        try {
            if (Schema::hasTable('alternative_pros_cons')) {
                $this->prosConsBreakdown = AlternativeProsCon::query()
                    ->select('type', DB::raw('count(*) as c'), DB::raw('sum(votes_count) as v'))
                    ->where('is_approved', true)
                    ->groupBy('type')
                    ->get()
                    ->mapWithKeys(fn ($r) => [$r->type => ['count' => (int) $r->c, 'votes' => (int) $r->v]])
                    ->all();
            }
        } catch (\Throwable) {
            $this->prosConsBreakdown = [];
        }
    }

    protected function count(string $table, callable $fn): int
    {
        try {
            if (! Schema::hasTable($table)) {
                return 0;
            }

            return (int) $fn();
        } catch (\Throwable) {
            return 0;
        }
    }
}
