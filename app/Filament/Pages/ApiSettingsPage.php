<?php

namespace App\Filament\Pages;

use App\Models\ApiKey;
use App\Models\User;
use App\Support\ApiSettings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

class ApiSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationLabel = 'API access';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.api-settings';

    protected static ?string $title = 'API access';

    protected static ?string $slug = 'api-access';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        $this->form->fill([
            'api_globally_enabled' => ApiSettings::isGloballyEnabled(),
            'api_require_key' => ApiSettings::requireKey(),
            'api_rate_limit_anon' => ApiSettings::rateLimitAnonymous(),
            'api_rate_limit_key' => ApiSettings::rateLimitAuthenticated(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Global API switch')
                    ->description('Turn the public JSON API on or off for the whole site. When off, every /api/* request returns HTTP 503.')
                    ->schema([
                        Toggle::make('api_globally_enabled')
                            ->label('Enable public API')
                            ->helperText('Off = nobody can use the API (with or without a key).')
                            ->inline(false),
                        Toggle::make('api_require_key')
                            ->label('Require API key for all endpoints')
                            ->helperText('On = anonymous requests without a key are rejected (401).')
                            ->inline(false),
                        TextInput::make('api_rate_limit_anon')
                            ->label('Anonymous requests per minute')
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(1000)
                            ->helperText('IP-based limit when no API key is sent.'),
                        TextInput::make('api_rate_limit_key')
                            ->label('API key requests per minute')
                            ->numeric()
                            ->minValue(30)
                            ->maxValue(5000)
                            ->helperText('Higher limit for valid keys.'),
                        Placeholder::make('docs')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300">'
                                .'Public docs: <a class="text-primary-600 underline" href="'.e(url('/api-docs')).'" target="_blank">/api-docs</a>. '
                                .'Per-user: <strong>System → Users → Allow API access</strong>. '
                                .'Keys: <strong>Engagement → API keys</strong>.'
                                .'</p>'
                            )),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        ApiSettings::setGloballyEnabled(! empty($state['api_globally_enabled']));
        ApiSettings::setRequireKey(! empty($state['api_require_key']));
        ApiSettings::setRateLimits(
            (int) ($state['api_rate_limit_anon'] ?? 60),
            (int) ($state['api_rate_limit_key'] ?? 600)
        );

        Notification::make()
            ->title('API settings saved')
            ->body(
                ApiSettings::isGloballyEnabled()
                    ? 'Public API is ON. Rate limits updated.'
                    : 'Public API is OFF — all /api routes return 503.'
            )
            ->success()
            ->send();
    }

    public function getStatsProperty(): array
    {
        $stats = [
            'keys_total' => 0,
            'keys_active' => 0,
            'users_api_off' => 0,
            'has_user_column' => false,
        ];

        try {
            if (Schema::hasTable('api_keys')) {
                $stats['keys_total'] = ApiKey::query()->count();
                $stats['keys_active'] = ApiKey::query()->whereNull('revoked_at')->count();
            }
            if (Schema::hasColumn('users', 'api_enabled')) {
                $stats['has_user_column'] = true;
                $stats['users_api_off'] = User::query()->where('api_enabled', false)->count();
            }
        } catch (\Throwable) {
        }

        return $stats;
    }
}
