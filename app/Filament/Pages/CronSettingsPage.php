<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\CronSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class CronSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Cron settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.cron-settings';

    protected static ?string $title = 'Cron & scheduled jobs';

    public ?array $data = [];

    public string $lastOutput = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $d = CronSettings::defaults();

        $this->form->fill([
            'cron_metrics_enabled' => CronSettings::get('cron_metrics_enabled', $d['cron_metrics_enabled']),
            'cron_metrics_time' => CronSettings::get('cron_metrics_time', $d['cron_metrics_time']),
            'cron_metrics_limit' => CronSettings::get('cron_metrics_limit', $d['cron_metrics_limit']),
            'cron_links_enabled' => CronSettings::get('cron_links_enabled', $d['cron_links_enabled']),
            'cron_links_day' => CronSettings::get('cron_links_day', $d['cron_links_day']),
            'cron_links_time' => CronSettings::get('cron_links_time', $d['cron_links_time']),
            'cron_links_limit' => CronSettings::get('cron_links_limit', $d['cron_links_limit']),
            'cron_digest_enabled' => CronSettings::get('cron_digest_enabled', $d['cron_digest_enabled']),
            'cron_digest_day' => CronSettings::get('cron_digest_day', $d['cron_digest_day']),
            'cron_digest_time' => CronSettings::get('cron_digest_time', $d['cron_digest_time']),
            'cron_notif_digest_enabled' => CronSettings::get('cron_notif_digest_enabled', $d['cron_notif_digest_enabled']),
            'cron_notif_digest_day' => CronSettings::get('cron_notif_digest_day', $d['cron_notif_digest_day']),
            'cron_notif_digest_time' => CronSettings::get('cron_notif_digest_time', $d['cron_notif_digest_time']),
            'cron_expire_sponsored_enabled' => CronSettings::get('cron_expire_sponsored_enabled', $d['cron_expire_sponsored_enabled']),
            'cron_expire_sponsored_time' => CronSettings::get('cron_expire_sponsored_time', $d['cron_expire_sponsored_time']),
        ]);
    }

    public function form(Form $form): Form
    {
        $dayOptions = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return $form
            ->schema([
                Section::make('GitHub metrics sync')
                    ->description('Pulls stars, forks, issues and recalculates health scores. Runs a limited batch each day so GitHub rate limits stay safe.')
                    ->schema([
                        Toggle::make('cron_metrics_enabled')->label('Enable scheduled metrics sync')->inline(false),
                        TextInput::make('cron_metrics_time')->label('Time (server timezone)')->placeholder('03:15')->helperText('24-hour format HH:MM'),
                        TextInput::make('cron_metrics_limit')->label('Max alternatives per run')->numeric()->minValue(1)->maxValue(200),
                    ])->columns(3),

                Section::make('Link health checks')
                    ->description('HEAD/GET requests to repo and website URLs. Marks broken links on each alternative.')
                    ->schema([
                        Toggle::make('cron_links_enabled')->label('Enable scheduled link checks')->inline(false),
                        Select::make('cron_links_day')->label('Day of week')->options($dayOptions),
                        TextInput::make('cron_links_time')->label('Time')->placeholder('04:00'),
                        TextInput::make('cron_links_limit')->label('Max alternatives per run')->numeric()->minValue(1)->maxValue(200),
                    ])->columns(2),

                Section::make('Weekly newsletter digest')
                    ->description('Also requires Email settings (SMTP/Resend) and digest toggles on that page.')
                    ->schema([
                        Toggle::make('cron_digest_enabled')->label('Enable scheduled newsletter digest')->inline(false),
                        Select::make('cron_digest_day')->label('Day of week')->options($dayOptions),
                        TextInput::make('cron_digest_time')->label('Time')->placeholder('09:00'),
                    ])->columns(3),

                Section::make('Member notification digest')
                    ->description('Emails signed-in members a summary of unread in-app notifications.')
                    ->schema([
                        Toggle::make('cron_notif_digest_enabled')->label('Enable member notification digest')->inline(false),
                        Select::make('cron_notif_digest_day')->label('Day of week')->options($dayOptions),
                        TextInput::make('cron_notif_digest_time')->label('Time')->placeholder('09:30'),
                    ])->columns(3),

                Section::make('Sponsored placements')
                    ->schema([
                        Toggle::make('cron_expire_sponsored_enabled')->label('Clear expired sponsored flags daily')->inline(false),
                        TextInput::make('cron_expire_sponsored_time')->label('Time')->placeholder('00:30'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $s = $this->form->getState();

        SiteSetting::setMany([
            'cron_metrics_enabled' => ! empty($s['cron_metrics_enabled']),
            'cron_metrics_time' => $this->normalizeTime($s['cron_metrics_time'] ?? '03:15'),
            'cron_metrics_limit' => max(1, min(200, (int) ($s['cron_metrics_limit'] ?? 25))),
            'cron_links_enabled' => ! empty($s['cron_links_enabled']),
            'cron_links_day' => (int) ($s['cron_links_day'] ?? 1),
            'cron_links_time' => $this->normalizeTime($s['cron_links_time'] ?? '04:00'),
            'cron_links_limit' => max(1, min(200, (int) ($s['cron_links_limit'] ?? 40))),
            'cron_digest_enabled' => ! empty($s['cron_digest_enabled']),
            'cron_digest_day' => (int) ($s['cron_digest_day'] ?? 1),
            'cron_digest_time' => $this->normalizeTime($s['cron_digest_time'] ?? '09:00'),
            'cron_notif_digest_enabled' => ! empty($s['cron_notif_digest_enabled']),
            'cron_notif_digest_day' => (int) ($s['cron_notif_digest_day'] ?? 1),
            'cron_notif_digest_time' => $this->normalizeTime($s['cron_notif_digest_time'] ?? '09:30'),
            'cron_expire_sponsored_enabled' => ! empty($s['cron_expire_sponsored_enabled']),
            'cron_expire_sponsored_time' => $this->normalizeTime($s['cron_expire_sponsored_time'] ?? '00:30'),
        ]);

        Notification::make()
            ->title('Cron settings saved')
            ->body('Laravel reads these the next time schedule:run executes. Hostinger must call schedule:run every minute (or every few minutes).')
            ->success()
            ->send();
    }

    protected function normalizeTime(mixed $value): string
    {
        $v = trim((string) $value);
        if (preg_match('/^\d{1,2}:\d{2}$/', $v)) {
            [$h, $m] = array_map('intval', explode(':', $v));

            return sprintf('%02d:%02d', max(0, min(23, $h)), max(0, min(59, $m)));
        }

        return '03:15';
    }

    public function runScheduleOnce(): void
    {
        try {
            Artisan::call('schedule:run');
            $this->lastOutput = Artisan::output() ?: 'schedule:run completed (no due jobs right now is normal).';
            CronSettings::markScheduleRan();
            Notification::make()->title('schedule:run finished')->body(trim($this->lastOutput))->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('schedule:run failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function runMetricsNow(): void
    {
        $limit = max(1, min(200, (int) CronSettings::get('cron_metrics_limit', 25)));
        try {
            Artisan::call('alternova:sync-metrics', ['--limit' => $limit]);
            $this->lastOutput = Artisan::output();
            Notification::make()->title('Metrics sync finished')->body(trim($this->lastOutput) ?: 'Done.')->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Metrics sync failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function runLinksNow(): void
    {
        $limit = max(1, min(200, (int) CronSettings::get('cron_links_limit', 40)));
        try {
            Artisan::call('alternova:check-links', ['--limit' => $limit]);
            $this->lastOutput = Artisan::output();
            Notification::make()->title('Link check finished')->body(trim($this->lastOutput) ?: 'Done.')->success()->send();
        } catch (\Throwable $e) {
            $this->lastOutput = $e->getMessage();
            Notification::make()->title('Link check failed')->body($e->getMessage())->danger()->send();
        }
    }

    public function getCronCommandProperty(): string
    {
        return CronSettings::hostingerCommand();
    }

    public function getJobsProperty(): array
    {
        return CronSettings::jobsOverview();
    }

    public function getLastRunProperty(): ?string
    {
        return CronSettings::lastScheduleRun();
    }
}
