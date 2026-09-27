<?php

namespace App\Filament\Widgets;

use App\Models\DomainSearchLog;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tools = ProprietaryTool::query()->count();
        $alts = OpenSourceAlternative::query()->count();
        $published = OpenSourceAlternative::query()->where('is_published', true)->count();
        $searches = DomainSearchLog::query()->count();

        return [
            Stat::make('Proprietary tools', $tools)
                ->description('Curated products')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Open-source alternatives', $alts)
                ->description($published . ' published')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('success'),
            Stat::make('Domain searches', $searches)
                ->description('Combinator usage')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }
}
