<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\AdSettings as AdConfig;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class AdSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationLabel = 'Ad settings';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.ad-settings';

    protected static ?string $title = 'Ad settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $fill = [
            'ads_enabled' => SiteSetting::getBool('ads_enabled', false),
            'ads_show_placeholders' => SiteSetting::getBool('ads_show_placeholders', false),
            'ads_head_code' => SiteSetting::get('ads_head_code', ''),
            'adsense_client' => SiteSetting::get('adsense_client', (string) config('ads.adsense.client')),
        ];

        foreach (AdConfig::placements() as $p) {
            $fill['ads_code_'.$p] = SiteSetting::get('ads_code_'.$p, '');
            $fill['adsense_slot_'.$p] = SiteSetting::get('adsense_slot_'.$p, (string) config('ads.adsense.slots.'.$p, ''));
        }

        $this->form->fill($fill);
    }

    public function form(Form $form): Form
    {
        $placementSections = [];
        $labels = AdConfig::placementLabels();

        foreach (AdConfig::placements() as $p) {
            $placementSections[] = Section::make($labels[$p] ?? $p)
                ->description('Paste any ad network unit HTML/JS (AdSense, Media.net, Ezoic, Propeller, custom, etc.). If you only use AdSense slot IDs, fill the optional slot field instead.')
                ->schema([
                    Textarea::make('ads_code_'.$p)
                        ->label('Ad code (HTML / JavaScript)')
                        ->rows(5)
                        ->columnSpanFull()
                        ->helperText('Preferred. Paste the full snippet from your ad network. Leave empty to use the AdSense slot ID below.')
                        ->extraInputAttributes(['class' => 'font-mono text-xs']),
                    TextInput::make('adsense_slot_'.$p)
                        ->label('Optional AdSense slot ID only')
                        ->placeholder('1234567890')
                        ->maxLength(32)
                        ->helperText('Used only when the HTML box above is empty and a Publisher client ID is set.'),
                ])
                ->columns(1)
                ->collapsed();
        }

        return $form
            ->schema([
                Section::make('Master controls')
                    ->schema([
                        Placeholder::make('intro')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-700 dark:text-gray-300 space-y-2 leading-relaxed">'
                                .'<p>This works with <strong>any</strong> ad network. Paste full unit codes per placement, or use AdSense client + slot IDs.</p>'
                                .'<p>Custom HTML takes priority over AdSense slot IDs for the same placement.</p>'
                                .'</div>'
                            )),
                        Toggle::make('ads_enabled')
                            ->label('Enable live ads')
                            ->helperText('When on, filled placements render on the public site.')
                            ->inline(false),
                        Toggle::make('ads_show_placeholders')
                            ->label('Show layout placeholders')
                            ->helperText('Dashed boxes where ads will appear. Helpful while designing; turn off for visitors.')
                            ->inline(false),
                    ])
                    ->columns(2),

                Section::make('Global head scripts')
                    ->description('Loaded once in &lt;head&gt; on public pages when ads are enabled. Use for AdSense auto ads, Ezoic, Mediavine, Raptive, or any loader script.')
                    ->schema([
                        Textarea::make('ads_head_code')
                            ->label('Head script / verification code')
                            ->rows(6)
                            ->columnSpanFull()
                            ->extraInputAttributes(['class' => 'font-mono text-xs'])
                            ->helperText('Example: AdSense script tag, or your network’s site-wide loader.'),
                        TextInput::make('adsense_client')
                            ->label('Google AdSense publisher ID (optional)')
                            ->placeholder('ca-pub-xxxxxxxxxxxxxxxx')
                            ->maxLength(64)
                            ->helperText('Only needed if you use AdSense slot IDs instead of pasting full HTML units.'),
                    ]),

                ...$placementSections,
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $payload = [
            'ads_enabled' => ! empty($state['ads_enabled']),
            'ads_show_placeholders' => ! empty($state['ads_show_placeholders']),
            'ads_head_code' => (string) ($state['ads_head_code'] ?? ''),
            'adsense_client' => trim((string) ($state['adsense_client'] ?? '')),
        ];

        foreach (AdConfig::placements() as $p) {
            $payload['ads_code_'.$p] = (string) ($state['ads_code_'.$p] ?? '');
            $payload['adsense_slot_'.$p] = trim((string) ($state['adsense_slot_'.$p] ?? ''));
        }

        SiteSetting::setMany($payload);

        Notification::make()
            ->title('Ad settings saved')
            ->body(
                ! empty($state['ads_enabled'])
                    ? 'Live ads are enabled for placements that have code or AdSense slots.'
                    : 'Live ads stay off. Placeholders follow your toggle.'
            )
            ->success()
            ->send();
    }
}
