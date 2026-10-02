<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class AffiliateSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Affiliate links';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.affiliate-settings';

    protected static ?string $title = 'Domain affiliate links';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'affiliate_namecheap' => SiteSetting::get('affiliate_namecheap', ''),
            'affiliate_porkbun' => SiteSetting::get('affiliate_porkbun', ''),
            'affiliate_godaddy' => SiteSetting::get('affiliate_godaddy', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('How this works')
                    ->schema([
                        Placeholder::make('help')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-700 dark:text-gray-300 space-y-2 leading-relaxed">'
                                .'<p><strong>Affiliate clicks</strong> (under Engagement) is only a log of when visitors click “Register at Namecheap / Porkbun / GoDaddy” on the Domain Combinator. You do not enter links there.</p>'
                                .'<p>Put your affiliate IDs / tracking codes <strong>here</strong>. When someone clicks a registrar button, Alternova redirects through <code class="text-xs">/go/{provider}?domain=…</code>, records the click, then sends them to the registrar with your ID attached.</p>'
                                .'<ul class="list-disc pl-5 space-y-1">'
                                .'<li><strong>Namecheap</strong> — Username or affiliate ID (appended as <code class="text-xs">aff=</code>)</li>'
                                .'<li><strong>Porkbun</strong> — Coupon / affiliate code (appended as <code class="text-xs">coupon=</code>)</li>'
                                .'<li><strong>GoDaddy</strong> — ISC / tracking code (appended as <code class="text-xs">isc=</code>)</li>'
                                .'</ul>'
                                .'<p class="text-xs text-gray-500">Leave a field blank to send visitors to the registrar without an affiliate parameter.</p>'
                                .'</div>'
                            )),
                    ]),
                Section::make('Registrar affiliate IDs')
                    ->schema([
                        TextInput::make('affiliate_namecheap')
                            ->label('Namecheap affiliate ID')
                            ->maxLength(120)
                            ->placeholder('e.g. your username or numeric ID'),
                        TextInput::make('affiliate_porkbun')
                            ->label('Porkbun coupon / affiliate code')
                            ->maxLength(120),
                        TextInput::make('affiliate_godaddy')
                            ->label('GoDaddy ISC / tracking code')
                            ->maxLength(120),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        SiteSetting::set('affiliate_namecheap', trim((string) ($state['affiliate_namecheap'] ?? '')));
        SiteSetting::set('affiliate_porkbun', trim((string) ($state['affiliate_porkbun'] ?? '')));
        SiteSetting::set('affiliate_godaddy', trim((string) ($state['affiliate_godaddy'] ?? '')));

        Notification::make()
            ->title('Affiliate settings saved')
            ->body('Domain Combinator register links will use these IDs.')
            ->success()
            ->send();
    }
}
