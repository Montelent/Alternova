<?php

namespace App\Filament\Widgets;

use App\Models\OpenSourceAlternative;
use App\Support\CronSettings;
use App\Support\IntegrationsSettings;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class SystemStatusWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $cron = CronSettings::lastScheduleRun();
        $cronLabel = $cron
            ? \Illuminate\Support\Carbon::parse($cron)->diffForHumans()
            : 'Not detected';

        $broken = 0;
        if (Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
            $broken = OpenSourceAlternative::query()
                ->where(function ($q) {
                    $q->where('repo_reachable', false)->orWhere('website_reachable', false);
                })
                ->count();
        }

        $github = IntegrationsSettings::githubToken() ? 'Configured' : 'Missing';

        return [
            Stat::make('Cron heartbeat', $cronLabel)
                ->description($cron ? 'schedule:run is reaching this install' : 'Add cron from Cron settings')
                ->descriptionIcon('heroicon-m-clock')
                ->color($cron ? 'success' : 'warning'),
            Stat::make('Broken links', (string) $broken)
                ->description('Repo or website unreachable')
                ->descriptionIcon('heroicon-m-link')
                ->color($broken > 0 ? 'danger' : 'success')
                ->url(\App\Filament\Pages\LinkHealthPage::getUrl()),
            Stat::make('GitHub API', $github)
                ->description('Token for metrics sync')
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color(IntegrationsSettings::githubToken() ? 'success' : 'gray')
                ->url(\App\Filament\Pages\IntegrationsPage::getUrl()),
        ];
    }
}
