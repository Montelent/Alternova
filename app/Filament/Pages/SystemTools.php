<?php

namespace App\Filament\Pages;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\SiteSetting;
use App\Services\AlternativeCsvImporter;
use App\Services\DemoDataSeeder;
use App\Services\LinkHealthService;
use App\Support\Installer;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemTools extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'System tools';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.system-tools';

    protected static ?string $title = 'System tools';

    public string $lastOutput = '';

    public ?array $data = [];

    public bool $maintenanceOn = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'csv' => "proprietary_name,alternative_name,repo_url,website_url,description,license_type,difficulty,language,published,featured\n",
        ]);

        try {
            $this->maintenanceOn = SiteSetting::getBool('maintenance_mode', false);
        } catch (\Throwable) {
            $this->maintenanceOn = false;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('csv')
                    ->label('CSV content')
                    ->rows(10)
                    ->helperText('Headers: proprietary_name, alternative_name, repo_url, website_url, description, license_type, difficulty, language, published, featured'),
            ])
            ->statePath('data');
    }

    public function runMigrations(): void
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $this->lastOutput = Artisan::output();
            Notification::make()->title('Migrations completed')->body(trim($this->lastOutput) ?: 'No pending migrations.')->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Migration failed')->body($e->getMessage())->danger()->send();
        }
    }

    /**
     * Directly add missing SEO columns without relying on migration history.
     * Use this if saving Alternatives fails with "Unknown column focus_keyword".
     */
    public function repairSeoSchema(): void
    {
        $added = [];

        try {
            foreach (['open_source_alternatives', 'proprietary_tools'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $map = [
                    'focus_keyword' => fn (Blueprint $t) => $t->string('focus_keyword', 120)->nullable(),
                    'robots_meta' => fn (Blueprint $t) => $t->string('robots_meta', 80)->nullable(),
                    'canonical_url' => fn (Blueprint $t) => $t->string('canonical_url', 500)->nullable(),
                    'og_title' => fn (Blueprint $t) => $t->string('og_title', 120)->nullable(),
                    'og_description' => fn (Blueprint $t) => $t->string('og_description', 200)->nullable(),
                    'og_image_url' => fn (Blueprint $t) => $t->string('og_image_url', 500)->nullable(),
                ];

                foreach ($map as $col => $definition) {
                    if (Schema::hasColumn($table, $col)) {
                        continue;
                    }
                    Schema::table($table, function (Blueprint $blueprint) use ($definition) {
                        $definition($blueprint);
                    });
                    $added[] = "{$table}.{$col}";
                }
            }

            if (! Schema::hasTable('admin_activity_logs')) {
                Schema::create('admin_activity_logs', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('action', 80);
                    $table->string('subject_type', 120)->nullable();
                    $table->unsignedBigInteger('subject_id')->nullable();
                    $table->string('subject_label', 255)->nullable();
                    $table->json('properties')->nullable();
                    $table->string('ip_address', 45)->nullable();
                    $table->timestamps();
                });
                $added[] = 'table:admin_activity_logs';
            }

            if (! Schema::hasTable('slug_redirects')) {
                Schema::create('slug_redirects', function (Blueprint $table) {
                    $table->id();
                    $table->string('old_slug', 190);
                    $table->string('new_slug', 190);
                    $table->string('model_type', 80)->default('alternative');
                    $table->timestamps();
                });
                $added[] = 'table:slug_redirects';
            }

            $this->lastOutput = $added === []
                ? 'All SEO columns and support tables already exist. Nothing to repair.'
                : 'Added: '.implode(', ', $added);

            Notification::make()
                ->title('Schema repair complete')
                ->body($this->lastOutput)
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Schema repair failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function runScheduledSync(): void
    {
        try {
            Artisan::call('alternova:sync-metrics', ['--limit' => 25]);
            $this->lastOutput = Artisan::output();
            Notification::make()->title('Scheduled-style sync finished')->body(trim($this->lastOutput))->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Sync failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function runScheduledLinkCheck(): void
    {
        try {
            Artisan::call('alternova:check-links', ['--limit' => 40]);
            $this->lastOutput = Artisan::output();
            Notification::make()->title('Link check finished')->body(trim($this->lastOutput))->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Link check failed')->body($e->getMessage())->danger()->send();
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
        Notification::make()->title('GitHub sync finished')->body("{$ok} updated, {$fail} failed.")->success()->send();
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
        Notification::make()->title('Link check finished')->body($this->lastOutput)->success()->send();
    }

    public function seedDemoData(): void
    {
        $result = app(DemoDataSeeder::class)->seed(force: false);
        $this->lastOutput = $result['message'];
        Notification::make()->title('Demo data')->body($result['message'])->success()->send();
    }

    public function seedDemoDataForce(): void
    {
        $result = app(DemoDataSeeder::class)->seed(force: true);
        $this->lastOutput = $result['message'];
        Notification::make()->title('Demo data (force)')->body($result['message'])->success()->send();
    }

    public function importCsv(): void
    {
        $csv = (string) ($this->form->getState()['csv'] ?? '');
        $result = app(AlternativeCsvImporter::class)->importFromString($csv);
        $this->lastOutput = "Imported {$result['imported']}, skipped {$result['skipped']}.\n".implode("\n", $result['errors']);
        Notification::make()->title('CSV import')->body("Imported {$result['imported']} row(s).")->success()->send();
    }

    public function exportCatalog(): StreamedResponse
    {
        $rows = OpenSourceAlternative::query()
            ->with('proprietaryTool')
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, [
                'name', 'slug', 'proprietary', 'repo_url', 'website_url', 'license',
                'language', 'difficulty', 'health', 'votes', 'published', 'featured',
            ]);
            foreach ($rows as $r) {
                fputcsv($h, [
                    $r->name,
                    $r->slug,
                    $r->proprietaryTool?->name,
                    $r->repo_url,
                    $r->website_url,
                    $r->license_type,
                    $r->primary_language,
                    $r->self_host_difficulty,
                    $r->overall_health_score,
                    $r->votes_count,
                    $r->is_published ? 1 : 0,
                    $r->is_featured ? 1 : 0,
                ]);
            }
            fclose($h);
        }, 'alternova-catalog-'.date('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function toggleMaintenance(): void
    {
        $next = ! SiteSetting::getBool('maintenance_mode', false);
        SiteSetting::set('maintenance_mode', $next);
        SiteSetting::set(
            'maintenance_message',
            'Alternova is temporarily offline for maintenance. Please check back soon.'
        );
        Cache::forget('site_settings');
        $this->maintenanceOn = $next;
        $this->lastOutput = $next
            ? 'Maintenance mode ON — public site returns 503. Admin still works.'
            : 'Maintenance mode OFF — public site is live.';

        Notification::make()
            ->title($next ? 'Maintenance enabled' : 'Maintenance disabled')
            ->{$next ? 'warning' : 'success'}()
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
            Notification::make()->title('Caches cleared')->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Cache clear failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function unlockInstaller(): void
    {
        Installer::unlock();
        $this->lastOutput = 'Installer unlocked. Visit /install to run the wizard again.';
        Notification::make()->title('Installer unlocked')->warning()->body($this->lastOutput)->send();
    }
}
