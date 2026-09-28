<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AdSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Ad settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.ad-settings';

    protected static ?string $title = 'Ad settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ads_enabled' => SiteSetting::getBool('ads_enabled', false),
            'ads_show_placeholders' => SiteSetting::getBool('ads_show_placeholders', false),
            'adsense_client' => SiteSetting::get('adsense_client', config('ads.adsense.client')),
            'adsense_slot_header' => SiteSetting::get('adsense_slot_header', config('ads.adsense.slots.header')),
            'adsense_slot_in_article' => SiteSetting::get('adsense_slot_in_article', config('ads.adsense.slots.in_article')),
            'adsense_slot_sidebar' => SiteSetting::get('adsense_slot_sidebar', config('ads.adsense.slots.sidebar')),
            'adsense_slot_footer' => SiteSetting::get('adsense_slot_footer', config('ads.adsense.slots.footer')),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Master controls')
                    ->description('Turn ads on only after Google AdSense (or your network) has approved the site.')
                    ->schema([
                        Forms\Components\Toggle::make('ads_enabled')
                            ->label('Enable live ads')
                            ->helperText('When on, AdSense scripts load on public pages that have a slot ID filled in.')
                            ->inline(false),
                        Forms\Components\Toggle::make('ads_show_placeholders')
                            ->label('Show layout placeholders')
                            ->helperText('Dashed boxes where ads will appear. Useful while designing; leave off in production.')
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Google AdSense')
                    ->schema([
                        Forms\Components\TextInput::make('adsense_client')
                            ->label('Publisher client ID')
                            ->placeholder('ca-pub-xxxxxxxxxxxxxxxx')
                            ->helperText('From AdSense → Sites / Ads → your ca-pub-… ID')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('adsense_slot_header')
                            ->label('Header ad unit slot ID')
                            ->placeholder('1234567890'),
                        Forms\Components\TextInput::make('adsense_slot_in_article')
                            ->label('In-article ad unit slot ID')
                            ->placeholder('1234567890'),
                        Forms\Components\TextInput::make('adsense_slot_sidebar')
                            ->label('Sidebar ad unit slot ID')
                            ->placeholder('1234567890'),
                        Forms\Components\TextInput::make('adsense_slot_footer')
                            ->label('Footer ad unit slot ID')
                            ->placeholder('1234567890'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        SiteSetting::setMany([
            'ads_enabled' => ! empty($state['ads_enabled']),
            'ads_show_placeholders' => ! empty($state['ads_show_placeholders']),
            'adsense_client' => trim((string) ($state['adsense_client'] ?? '')),
            'adsense_slot_header' => trim((string) ($state['adsense_slot_header'] ?? '')),
            'adsense_slot_in_article' => trim((string) ($state['adsense_slot_in_article'] ?? '')),
            'adsense_slot_sidebar' => trim((string) ($state['adsense_slot_sidebar'] ?? '')),
            'adsense_slot_footer' => trim((string) ($state['adsense_slot_footer'] ?? '')),
        ]);

        Notification::make()
            ->title('Ad settings saved')
            ->body(
                ! empty($state['ads_enabled'])
                    ? 'Live ads are enabled on public pages with slot IDs.'
                    : 'Live ads stay off. Placeholders follow your toggle.'
            )
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Save ad settings')
                ->submit('save'),
        ];
    }
}
