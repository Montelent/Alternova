<?php

namespace App\Filament\Pages;

use App\Models\OpenSourceAlternative;
use App\Services\LinkHealthService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;

class LinkHealthPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Link health';

    protected static ?string $navigationGroup = 'Open Source Finder';

    protected static ?int $navigationSort = 25;

    protected static string $view = 'filament.pages.link-health';

    protected static ?string $title = 'Link health';

    public string $lastOutput = '';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function brokenAlternatives()
    {
        if (! Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
            return collect();
        }

        return OpenSourceAlternative::query()
            ->where(function ($q) {
                $q->where('repo_reachable', false)
                    ->orWhere('website_reachable', false);
            })
            ->orderByDesc('links_checked_at')
            ->limit(100)
            ->get();
    }

    public function stats(): array
    {
        if (! Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
            return ['broken' => 0, 'checked' => 0, 'never' => 0];
        }

        $broken = OpenSourceAlternative::query()
            ->where(function ($q) {
                $q->where('repo_reachable', false)->orWhere('website_reachable', false);
            })
            ->count();

        $checked = OpenSourceAlternative::query()->whereNotNull('links_checked_at')->count();
        $never = OpenSourceAlternative::query()->whereNull('links_checked_at')->count();

        return compact('broken', 'checked', 'never');
    }

    public function recheck(int $id): void
    {
        $alt = OpenSourceAlternative::query()->find($id);
        if (! $alt) {
            return;
        }

        try {
            app(LinkHealthService::class)->checkAlternative($alt);
            $this->lastOutput = 'Rechecked: '.$alt->name;
            Notification::make()->title('Link rechecked')->body($alt->name)->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Recheck failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function recheckAllBroken(): void
    {
        $items = $this->brokenAlternatives();
        $ok = 0;
        foreach ($items as $alt) {
            try {
                app(LinkHealthService::class)->checkAlternative($alt);
                $ok++;
            } catch (\Throwable) {
            }
        }
        $this->lastOutput = "Rechecked {$ok} alternative(s).";
        Notification::make()->title('Batch recheck finished')->body($this->lastOutput)->success()->send();
    }
}
