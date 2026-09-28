<?php

namespace App\Filament\Pages;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Services\LinkHealthService;
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

        $ok = 0;
        $fail = 0;
        $lines = [];

        foreach ($alts as $alt) {
            try {
                SyncGitHubMetricsJob::dispatchSync($alt);
                $fresh = $alt->fresh();
                $ok++;
                $lines[] = $fresh->name.': health '.$fresh->overall_health_score;
            } catch (\Throwable $e) {
                $fail++;
                $lines[] = $alt->name.': FAILED '.$e->getMessage();
            }
        }

        $this->lastOutput = "Synced {$ok} OK, {$fail} failed.\n".implode("\n", $lines);

        Notification::make()
            ->title('GitHub sync finished')
            ->body("{$ok} updated, {$fail} failed. See output below.")
            ->success()
            ->send();
    }

    public function checkAllLinks(): void
    {
        $service = app(LinkHealthService::class);
        $alts = OpenSourceAlternative::query()->get();
        $broken = 0;
        $checked = 0;

        foreach ($alts as $alt) {
            $service->checkAlternative($alt);
            $checked++;
            if ($alt->fresh()->hasBrokenLinks()) {
                $broken++;
            }
        }

        $this->lastOutput = "Checked {$checked} alternative(s). {$broken} have at least one unreachable link.";

        Notification::make()
            ->title('Link check finished')
            ->body($this->lastOutput)
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

            $this->lastOutput = 'Config, route, view, and application cache cleared.';

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
