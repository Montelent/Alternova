<?php

namespace App\Filament\Widgets;

use App\Models\AlternativeSubmission;
use App\Models\DomainSearchLog;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tools = ProprietaryTool::query()->count();
        $alts = OpenSourceAlternative::query()->count();
        $published = OpenSourceAlternative::query()->where('is_published', true)->count();
        $featured = OpenSourceAlternative::query()->where('is_featured', true)->where('is_published', true)->count();

        $searches = 0;
        try {
            if (Schema::hasTable('domain_search_logs')) {
                $searches = DomainSearchLog::query()->count();
            }
        } catch (\Throwable) {
        }

        $pending = 0;
        try {
            if (Schema::hasTable('alternative_submissions')) {
                $pending = AlternativeSubmission::query()->where('status', 'pending')->count();
            }
        } catch (\Throwable) {
        }

        return [
            Stat::make('Proprietary tools', $tools)
                ->description('Curated products')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Open-source alternatives', $alts)
                ->description($published.' published · '.$featured.' featured')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('success'),
            Stat::make('Domain searches', $searches)
                ->description('Combinator usage')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
            Stat::make('Pending submissions', $pending)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($pending > 0 ? 'warning' : 'gray'),
        ];
    }
}
