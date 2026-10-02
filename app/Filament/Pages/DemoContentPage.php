<?php

namespace App\Filament\Pages;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\DemoCatalogSeeder;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DemoContentPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Demo content';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.demo-content';

    protected static ?string $title = 'Demo content seeder';

    protected static ?string $slug = 'demo-content';

    public string $lastReport = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getCountsProperty(): array
    {
        try {
            return [
                'tools' => ProprietaryTool::query()->count(),
                'tools_published' => ProprietaryTool::query()->where('is_published', true)->count(),
                'alts' => OpenSourceAlternative::query()->count(),
                'alts_published' => OpenSourceAlternative::query()->where('is_published', true)->count(),
            ];
        } catch (\Throwable) {
            return ['tools' => 0, 'tools_published' => 0, 'alts' => 0, 'alts_published' => 0];
        }
    }

    public function seedDemo(): void
    {
        try {
            $result = app(DemoCatalogSeeder::class)->seed();
            $this->lastReport = sprintf(
                'Tools created: %d · Alternatives created: %d · Links synced: %d · Already existed (skipped): %d',
                $result['tools_created'],
                $result['alternatives_created'],
                $result['linked'],
                $result['skipped']
            );

            Notification::make()
                ->title('Demo content ready')
                ->body($this->lastReport)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastReport = $e->getMessage();
            Notification::make()->title('Seeder failed')->body($e->getMessage())->danger()->send();
        }
    }
}
