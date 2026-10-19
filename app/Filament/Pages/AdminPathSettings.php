<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\AdminPanel;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AdminPathSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationLabel = 'Admin URL path';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.admin-path-settings';

    protected static ?string $title = 'Admin panel URL path';

    protected static ?string $slug = 'admin-path';

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
            'admin_path' => SiteSetting::get('admin_path', 'admin') ?: 'admin',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Admin panel path')
                    ->description('Change the public URL of the admin panel (default /admin). Use a hard-to-guess path. After saving, clear caches and open the new URL — the old path will 404.')
                    ->schema([
                        TextInput::make('admin_path')
                            ->label('Path segment')
                            ->required()
                            ->maxLength(40)
                            ->prefix('/')
                            ->placeholder('admin')
                            ->helperText('Letters, numbers, hyphens, underscores only. Reserved: login, api, install, livewire…')
                            ->regex('/^[a-zA-Z0-9\-_]+$/')
                            ->live(debounce: 300),
                        Placeholder::make('preview')
                            ->label('Full admin URL')
                            ->content(function (Get $get) {
                                $path = strtolower(trim((string) $get('admin_path'), '/'));
                                $path = preg_replace('/[^a-z0-9\-_]/', '', $path) ?: 'admin';

                                return rtrim((string) config('app.url'), '/').'/'.$path;
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $path = strtolower(trim((string) ($data['admin_path'] ?? 'admin'), " \t\n\r\0\x0B/"));
        $path = preg_replace('/[^a-z0-9\-_]/', '', $path) ?? '';
        if ($path === '' || in_array($path, ['login', 'register', 'api', 'livewire', 'up', 'install'], true)) {
            $path = 'admin';
        }
        SiteSetting::set('admin_path', $path);

        Notification::make()
            ->title('Admin path saved')
            ->body('New URL: '.AdminPanel::url().' — run php artisan optimize:clear, then use the new path.')
            ->success()
            ->send();
    }
}
