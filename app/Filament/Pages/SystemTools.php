<?php

namespace App\Filament\Pages;

use App\Support\Installer;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class SystemTools extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'System Tools';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.system-tools';

    public ?string $lastOutput = null;

    public ?bool $lastSuccess = null;

    public static function canAccess(): bool
    {
        // Only authenticated users; tighten further with roles if you add them
        return Auth::check();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('migrate')
                ->label('Run Migrations')
                ->icon('heroicon-o-circle-stack')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Run database migrations')
                ->modalDescription('This will execute `php artisan migrate --force`. Only pending migrations will run. Continue?')
                ->modalSubmitActionLabel('Yes, run migrations')
                ->action(function () {
                    $this->runCommand('migrate', ['--force' => true]);
                }),

            Action::make('migrateStatus')
                ->label('Migration Status')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->action(function () {
                    $this->runCommand('migrate:status');
                }),

            Action::make('cacheClear')
                ->label('Clear Caches')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () {
                    $outputs = [];
                    foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $cmd) {
                        $result = Installer::runArtisan($cmd);
                        $outputs[] = "=== {$cmd} ===\n" . $result['output'];
                    }
                    $this->lastOutput = implode("\n", $outputs);
                    $this->lastSuccess = true;

                    Notification::make()
                        ->title('Caches cleared')
                        ->success()
                        ->send();
                }),

            Action::make('optimize')
                ->label('Optimize')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $this->runCommand('optimize');
                }),
        ];
    }

    protected function runCommand(string $command, array $parameters = []): void
    {
        try {
            $result = Installer::runArtisan($command, $parameters);
            $this->lastOutput = $result['output'] ?: '(no output)';
            $this->lastSuccess = $result['success'];

            Notification::make()
                ->title($result['success'] ? 'Command succeeded' : 'Command finished with errors')
                ->body("`{$command}` exit code: {$result['exit_code']}")
                ->{$result['success'] ? 'success' : 'danger'}()
                ->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            $this->lastSuccess = false;

            Notification::make()
                ->title('Command failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
