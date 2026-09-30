<?php

namespace App\Filament\Widgets;

use App\Models\AlternativeComment;
use App\Models\AlternativeProsCon;
use App\Models\AlternativeVote;
use App\Models\OpenSourceAlternative;
use App\Models\UserNotification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class EngagementStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $votes7 = $this->safe('alternative_votes', function () {
            return AlternativeVote::query()->where('created_at', '>=', now()->subDays(7))->count();
        });

        $commentsPending = $this->safe('alternative_comments', function () {
            return AlternativeComment::query()
                ->where('is_approved', false)
                ->where('is_hidden', false)
                ->count();
        });

        $prosConsPending = $this->safe('alternative_pros_cons', function () {
            return AlternativeProsCon::query()->where('is_approved', false)->count();
        });

        $prosConsTotal = $this->safe('alternative_pros_cons', function () {
            return AlternativeProsCon::query()->where('is_approved', true)->count();
        });

        $unreadNotifs = $this->safe('user_notifications', function () {
            return UserNotification::query()->whereNull('read_at')->count();
        });

        $topVoted = $this->safe('open_source_alternatives', function () {
            $alt = OpenSourceAlternative::query()
                ->where('is_published', true)
                ->orderByDesc('votes_count')
                ->first();

            return $alt ? $alt->name.' ('.(int) $alt->votes_count.')' : '—';
        }, '—');

        return [
            Stat::make('Votes (7 days)', $votes7)
                ->description('Upvotes on alternatives')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('success'),
            Stat::make('Comments pending', $commentsPending)
                ->description('Awaiting moderation')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($commentsPending > 0 ? 'warning' : 'gray'),
            Stat::make('Pros/cons pending', $prosConsPending)
                ->description($prosConsTotal.' approved live')
                ->descriptionIcon('heroicon-m-scale')
                ->color($prosConsPending > 0 ? 'warning' : 'gray'),
            Stat::make('Unread notifications', $unreadNotifs)
                ->description('Across all members')
                ->descriptionIcon('heroicon-m-bell')
                ->color($unreadNotifs > 0 ? 'info' : 'gray'),
            Stat::make('Top voted alternative', is_string($topVoted) ? $topVoted : '—')
                ->description('All-time votes')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('primary'),
        ];
    }

    protected function safe(string $table, callable $fn, mixed $default = 0): mixed
    {
        try {
            if (! Schema::hasTable($table)) {
                return $default;
            }

            return $fn();
        } catch (\Throwable) {
            return $default;
        }
    }
}
