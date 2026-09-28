<?php

namespace App\Filament\Pages;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Support\Installer;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SystemTools extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'System tools';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.system-tools';

    protected static ?string $title = 'System tools';

    public string $lastOutput = '';

    public function runMigrations(): void
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->lastOutput = Artisan::output();

            Notification::make()
                ->title('Migrations completed')
                ->body(trim($this->lastOutput) ?: 'No pending migrations.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()
                ->title('Migration failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncAllMetrics(): void
    {
        $alts = OpenSourceAlternative::query()
            ->whereNotNull('repo_url')
            ->where('repo_url', 'like', '%github.com%')
            ->get();

        $count = 0;
        foreach ($alts as $alt) {
            try {
                // sync driver runs immediately; otherwise queue
                SyncGitHubMetricsJob::dispatch($alt);
                $count++;
            } catch (\Throwable $e) {
                // continue others
            }
        }

        $this->lastOutput = "Dispatched metric sync for {$count} alternative(s).";

        Notification::make()
            ->title('GitHub sync queued')
            ->body($this->lastOutput.' With QUEUE_CONNECTION=sync this runs on the next requests.')
            ->success()
            ->send();
    }

    public function clearCaches(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Cache::flush();

            $views = storage_path('framework/views');
            if (is_dir($views)) {
                foreach (File::glob($views.'/*') as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }

            Cache::forget('site_settings');

            $this->lastOutput = "Config, route, view, and application cache cleared.";

            Notification::make()
                ->title('Caches cleared')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()
                ->title('Cache clear failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function unlockInstaller(): void
    {
        Installer::unlock();
        $this->lastOutput = 'Installer unlocked. Visit /install to run the wizard again.';

        Notification::make()
            ->title('Installer unlocked')
            ->warning()
            ->body($this->lastOutput)
            ->send();
    }
}
