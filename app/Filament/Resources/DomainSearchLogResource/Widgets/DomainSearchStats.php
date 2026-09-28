<?php

namespace App\Filament\Resources\DomainSearchLogResource\Widgets;

use App\Models\DomainSearchLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DomainSearchStats extends BaseWidget
{
    protected function getStats(): array
    {
        $total = DomainSearchLog::query()->count();
        $week = DomainSearchLog::query()->where('created_at', '>=', now()->subDays(7))->count();
        $avgGenerated = (float) DomainSearchLog::query()->avg('domain_generated_count');

        $topKeyword = '—';
        try {
            $logs = DomainSearchLog::query()
                ->whereNotNull('seed_keywords')
                ->latest()
                ->limit(200)
                ->pluck('seed_keywords');

            $freq = [];
            foreach ($logs as $keywords) {
                if (! is_array($keywords)) {
                    continue;
                }
                foreach ($keywords as $kw) {
                    $kw = strtolower(trim((string) $kw));
                    if ($kw === '') {
                        continue;
                    }
                    $freq[$kw] = ($freq[$kw] ?? 0) + 1;
                }
            }
            if ($freq) {
                arsort($freq);
                $topKeyword = array_key_first($freq).' ('.reset($freq).')';
            }
        } catch (\Throwable) {
            // ignore
        }

        return [
            Stat::make('Total searches', $total)->color('info'),
            Stat::make('Last 7 days', $week)->color('success'),
            Stat::make('Avg domains / search', number_format($avgGenerated, 1))->color('primary'),
            Stat::make('Top keyword', $topKeyword)->color('warning'),
        ];
    }
}
