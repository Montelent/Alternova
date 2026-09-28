<?php

namespace App\Filament\Widgets;

use App\Models\AlternativeSubmission;
use App\Models\DomainSearchLog;
use App\Models\IssueReport;
use App\Models\NewsletterSubscriber;
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

        $searches = $this->safeCount('domain_search_logs', fn () => DomainSearchLog::query()->count());
        $pending = $this->safeCount('alternative_submissions', fn () => AlternativeSubmission::query()->where('status', 'pending')->count());
        $openReports = $this->safeCount('issue_reports', fn () => IssueReport::query()->where('status', 'open')->count());
        $subscribers = $this->safeCount('newsletter_subscribers', fn () => NewsletterSubscriber::query()->where('status', 'active')->count());

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
            Stat::make('Open issue reports', $openReports)
                ->description('Community flags')
                ->descriptionIcon('heroicon-m-flag')
                ->color($openReports > 0 ? 'danger' : 'gray'),
            Stat::make('Newsletter subscribers', $subscribers)
                ->description('Active list')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success'),
        ];
    }

    protected function safeCount(string $table, callable $fn): int
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
