<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'Site SEO';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.site-seo-settings';

    protected static ?string $title = 'Site SEO';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'site_tagline' => SiteSetting::get('site_tagline', 'Open-source alternatives & brandable domains'),
            'default_meta_description' => SiteSetting::get(
                'default_meta_description',
                'Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.'
            ),
            'twitter_handle' => SiteSetting::get('twitter_handle', ''),
            'og_image_url' => SiteSetting::get('og_image_url', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Defaults')
                    ->description('Used when a page does not set its own title/description.')
                    ->schema([
                        TextInput::make('site_tagline')->label('Site tagline')->maxLength(120),
                        Textarea::make('default_meta_description')
                            ->label('Default meta description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Aim for ~150–160 characters.'),
                        TextInput::make('twitter_handle')
                            ->label('X / Twitter handle')
                            ->placeholder('@alternova')
                            ->maxLength(40),
                        TextInput::make('og_image_url')
                            ->label('Default Open Graph image URL')
                            ->url()
                            ->helperText('Absolute URL to a 1200×630 image.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setMany([
            'site_tagline' => trim((string) ($state['site_tagline'] ?? '')),
            'default_meta_description' => trim((string) ($state['default_meta_description'] ?? '')),
            'twitter_handle' => trim((string) ($state['twitter_handle'] ?? '')),
            'og_image_url' => trim((string) ($state['og_image_url'] ?? '')),
        ]);

        Notification::make()->title('Site SEO settings saved')->success()->send();
    }
}
