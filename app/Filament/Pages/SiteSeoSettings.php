<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\SeoManager;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?string $navigationLabel = 'SEO settings';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 12;

    protected static string $view = 'filament.pages.site-seo-settings';

    protected static ?string $title = 'SEO settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageSystem() ?? false;
    }

    public function mount(): void
    {
        $g = fn (string $k, mixed $d = '') => SiteSetting::get($k, $d);

        $this->form->fill([
            'seo_site_name' => $g('seo_site_name', config('app.name', 'Alternova')),
            'site_tagline' => $g('site_tagline', 'Open-source alternatives & brandable domains'),
            'seo_separator' => $g('seo_separator', '|'),
            'default_meta_description' => $g(
                'default_meta_description',
                'Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.'
            ),
            'seo_robots_default' => $g('seo_robots_default', 'index,follow,max-image-preview:large,max-snippet:-1'),
            'seo_site_noindex' => SiteSetting::getBool('seo_site_noindex', false),

            'seo_title_home' => $g('seo_title_home', '%sitename% %sep% Open-source alternatives & brandable domains'),
            'seo_desc_home' => $g('seo_desc_home', ''),
            'seo_title_alternative' => $g('seo_title_alternative', '%title% — Open-Source %prop% Alternative %sep% %sitename%'),
            'seo_desc_alternative' => $g('seo_desc_alternative', '%title% is a free, self-hostable open-source alternative to %prop%. %excerpt%'),
            'seo_title_tool' => $g('seo_title_tool', 'Open-source alternatives to %title% %sep% %sitename%'),
            'seo_desc_tool' => $g('seo_desc_tool', 'Browse free, self-hostable open-source alternatives to %title%. %excerpt%'),
            'seo_title_finder' => $g('seo_title_finder', 'Open Source Alternatives Finder %sep% %sitename%'),
            'seo_title_domains' => $g('seo_title_domains', 'Domain Name Idea Combinator %sep% %sitename%'),
            'seo_title_compare' => $g('seo_title_compare', 'Compare open-source alternatives %sep% %sitename%'),

            'twitter_handle' => $g('twitter_handle', ''),
            'twitter_card' => $g('twitter_card', 'summary_large_image'),
            'og_image_url' => $g('og_image_url', ''),
            'seo_facebook_app_id' => $g('seo_facebook_app_id', ''),

            'seo_verify_google' => $g('seo_verify_google', ''),
            'seo_verify_bing' => $g('seo_verify_bing', ''),
            'seo_verify_yandex' => $g('seo_verify_yandex', ''),
            'seo_verify_pinterest' => $g('seo_verify_pinterest', ''),

            'seo_org_name' => $g('seo_org_name', config('app.name', 'Alternova')),
            'seo_org_logo' => $g('seo_org_logo', ''),
            'seo_org_sameas' => $g('seo_org_sameas', ''),

            'seo_canonical_force_https' => SiteSetting::getBool('seo_canonical_force_https', true),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('How templates work (read this first)')
                    ->description('Similar to Rank Math / Yoast title templates. Variables below are replaced automatically when a page has no custom SEO title.')
                    ->schema([
                        Placeholder::make('token_guide')
                            ->label('Available variables')
                            ->content(new \Illuminate\Support\HtmlString(<<<'HTML'
<div class="text-sm space-y-3 text-gray-600 dark:text-gray-300">
  <p>Type these <strong>exactly</strong> (including the percent signs). They are not WordPress shortcodes — they only work inside the template fields on this page.</p>
  <div class="overflow-x-auto">
  <table class="min-w-full text-left text-xs border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
    <thead class="bg-gray-50 dark:bg-gray-800">
      <tr>
        <th class="px-3 py-2 font-semibold">Variable</th>
        <th class="px-3 py-2 font-semibold">Meaning</th>
        <th class="px-3 py-2 font-semibold">Example output</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
      <tr><td class="px-3 py-2 font-mono">%sitename%</td><td class="px-3 py-2">Your site name (General tab)</td><td class="px-3 py-2">Alternova</td></tr>
      <tr><td class="px-3 py-2 font-mono">%sep%</td><td class="px-3 py-2">Title separator</td><td class="px-3 py-2">|</td></tr>
      <tr><td class="px-3 py-2 font-mono">%tagline%</td><td class="px-3 py-2">Site tagline</td><td class="px-3 py-2">Open-source alternatives…</td></tr>
      <tr><td class="px-3 py-2 font-mono">%title%</td><td class="px-3 py-2">Record name (alternative or tool)</td><td class="px-3 py-2">AppFlowy</td></tr>
      <tr><td class="px-3 py-2 font-mono">%prop%</td><td class="px-3 py-2">Linked proprietary product name</td><td class="px-3 py-2">Notion</td></tr>
      <tr><td class="px-3 py-2 font-mono">%license%</td><td class="px-3 py-2">License type</td><td class="px-3 py-2">AGPL-3.0</td></tr>
      <tr><td class="px-3 py-2 font-mono">%language%</td><td class="px-3 py-2">Primary language</td><td class="px-3 py-2">Rust</td></tr>
      <tr><td class="px-3 py-2 font-mono">%health%</td><td class="px-3 py-2">Health score (0–100)</td><td class="px-3 py-2">82</td></tr>
      <tr><td class="px-3 py-2 font-mono">%excerpt%</td><td class="px-3 py-2">Short description excerpt</td><td class="px-3 py-2">Self-hostable notes…</td></tr>
      <tr><td class="px-3 py-2 font-mono">%page%</td><td class="px-3 py-2">Static page label (finder, domains…)</td><td class="px-3 py-2">Domain Combinator</td></tr>
    </tbody>
  </table>
  </div>
  <p class="pt-1"><strong>Example template:</strong> <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%title% — Open-Source %prop% Alternative %sep% %sitename%</code><br>
  <strong>Becomes:</strong> AppFlowy — Open-Source Notion Alternative | Alternova</p>
  <p><strong>Override order:</strong> 1) SEO title on the alternative/tool form → 2) template on this page → 3) built-in fallback.</p>
</div>
HTML)),
                        Placeholder::make('live_preview')
                            ->label('Live template preview (sample data)')
                            ->content(function (Get $get) {
                                $seo = app(SeoManager::class);
                                // Temporarily use form state via SiteSetting is not written yet — simulate replace
                                $sep = $get('seo_separator') ?: '|';
                                $site = $get('seo_site_name') ?: 'Alternova';
                                $tpl = $get('seo_title_alternative') ?: '%title% — Open-Source %prop% Alternative %sep% %sitename%';
                                $out = str_replace(
                                    ['%sitename%', '%sep%', '%title%', '%prop%', '%license%', '%language%', '%health%', '%excerpt%', '%tagline%', '%page%'],
                                    [$site, $sep, 'AppFlowy', 'Notion', 'AGPL-3.0', 'Rust', '82', 'A self-hostable Notion alternative.', $get('site_tagline') ?: '', 'Finder'],
                                    (string) $tpl
                                );

                                return 'Sample alternative title → '.$out;
                            }),
                    ]),

                Section::make('1. General')
                    ->schema([
                        TextInput::make('seo_site_name')->label('Site name')->required()->maxLength(80)
                            ->helperText('Used wherever %sitename% appears.'),
                        TextInput::make('site_tagline')->label('Tagline')->maxLength(120)
                            ->helperText('Used in footer and %tagline%.'),
                        TextInput::make('seo_separator')->label('Title separator')->maxLength(5)
                            ->helperText('Common choices: |  –  ·  >'),
                        Textarea::make('default_meta_description')
                            ->label('Fallback meta description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('When a page has no description at all (~150–160 characters).')
                            ->columnSpanFull(),
                        TextInput::make('seo_robots_default')
                            ->label('Default robots meta')
                            ->helperText('Usually: index,follow,max-image-preview:large,max-snippet:-1')
                            ->columnSpanFull(),
                        Toggle::make('seo_site_noindex')
                            ->label('Discourage search engines (noindex entire public site)')
                            ->helperText('Turn ON for staging/dev. Overrides every page.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('2. Title & meta templates')
                    ->schema([
                        TextInput::make('seo_title_home')->label('Homepage title template')->columnSpanFull(),
                        Textarea::make('seo_desc_home')->label('Homepage description (optional override)')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_alternative')->label('Open-source alternative — title template')->columnSpanFull()
                            ->helperText('Variables: %title% %prop% %license% %language% %health% %sitename% %sep%'),
                        Textarea::make('seo_desc_alternative')->label('Open-source alternative — description template')->rows(2)->columnSpanFull()
                            ->helperText('Variables: %title% %prop% %excerpt% %license% …'),
                        TextInput::make('seo_title_tool')->label('Proprietary tool — title template')->columnSpanFull()
                            ->helperText('Variables: %title% %sitename% %sep%'),
                        Textarea::make('seo_desc_tool')->label('Proprietary tool — description template')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_finder')->label('Finder page title')->helperText('Use %page% or plain text + %sep% %sitename%'),
                        TextInput::make('seo_title_domains')->label('Domains page title'),
                        TextInput::make('seo_title_compare')->label('Compare page title'),
                    ])->columns(2),

                Section::make('3. Social (Open Graph & X)')
                    ->schema([
                        TextInput::make('og_image_url')->label('Default social image URL')->url()
                            ->helperText('Absolute HTTPS URL, ideally 1200×630 pixels. Used when a page has no OG image.')
                            ->columnSpanFull(),
                        TextInput::make('twitter_handle')->label('X / Twitter handle')->placeholder('@alternova'),
                        Select::make('twitter_card')->label('Twitter card type')->options([
                            'summary_large_image' => 'Summary with large image (recommended)',
                            'summary' => 'Summary',
                        ]),
                        TextInput::make('seo_facebook_app_id')->label('Facebook App ID')->maxLength(40),
                    ])->columns(2),

                Section::make('4. Webmaster tools verification')
                    ->description('Paste only the content value from each platform — not the full HTML tag.')
                    ->schema([
                        TextInput::make('seo_verify_google')->label('Google Search Console')->placeholder('e.g. abc123…'),
                        TextInput::make('seo_verify_bing')->label('Bing Webmaster')->placeholder('e.g. abc123…'),
                        TextInput::make('seo_verify_yandex')->label('Yandex')->placeholder('e.g. abc123…'),
                        TextInput::make('seo_verify_pinterest')->label('Pinterest')->placeholder('e.g. abc123…'),
                    ])->columns(2)->collapsed(),

                Section::make('5. Organization schema (Knowledge Graph)')
                    ->schema([
                        TextInput::make('seo_org_name')->label('Organization name'),
                        TextInput::make('seo_org_logo')->label('Organization logo URL')->url()->columnSpanFull(),
                        Textarea::make('seo_org_sameas')
                            ->label('Social profile URLs (one per line)')
                            ->rows(4)
                            ->helperText('https://twitter.com/…\nhttps://github.com/…\nhttps://linkedin.com/company/…')
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),

                Section::make('6. Advanced')
                    ->schema([
                        Toggle::make('seo_canonical_force_https')
                            ->label('Prefer HTTPS when building defaults')
                            ->helperText('Keep ON in production if your site is served over HTTPS.'),
                    ])->collapsed(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $s = $this->form->getState();

        SiteSetting::setMany([
            'seo_site_name' => trim((string) ($s['seo_site_name'] ?? '')),
            'site_tagline' => trim((string) ($s['site_tagline'] ?? '')),
            'seo_separator' => trim((string) ($s['seo_separator'] ?? '|')) ?: '|',
            'default_meta_description' => trim((string) ($s['default_meta_description'] ?? '')),
            'seo_robots_default' => trim((string) ($s['seo_robots_default'] ?? '')),
            'seo_site_noindex' => ! empty($s['seo_site_noindex']),

            'seo_title_home' => trim((string) ($s['seo_title_home'] ?? '')),
            'seo_desc_home' => trim((string) ($s['seo_desc_home'] ?? '')),
            'seo_title_alternative' => trim((string) ($s['seo_title_alternative'] ?? '')),
            'seo_desc_alternative' => trim((string) ($s['seo_desc_alternative'] ?? '')),
            'seo_title_tool' => trim((string) ($s['seo_title_tool'] ?? '')),
            'seo_desc_tool' => trim((string) ($s['seo_desc_tool'] ?? '')),
            'seo_title_finder' => trim((string) ($s['seo_title_finder'] ?? '')),
            'seo_title_domains' => trim((string) ($s['seo_title_domains'] ?? '')),
            'seo_title_compare' => trim((string) ($s['seo_title_compare'] ?? '')),

            'twitter_handle' => trim((string) ($s['twitter_handle'] ?? '')),
            'twitter_card' => trim((string) ($s['twitter_card'] ?? 'summary_large_image')),
            'og_image_url' => trim((string) ($s['og_image_url'] ?? '')),
            'seo_facebook_app_id' => trim((string) ($s['seo_facebook_app_id'] ?? '')),

            'seo_verify_google' => trim((string) ($s['seo_verify_google'] ?? '')),
            'seo_verify_bing' => trim((string) ($s['seo_verify_bing'] ?? '')),
            'seo_verify_yandex' => trim((string) ($s['seo_verify_yandex'] ?? '')),
            'seo_verify_pinterest' => trim((string) ($s['seo_verify_pinterest'] ?? '')),

            'seo_org_name' => trim((string) ($s['seo_org_name'] ?? '')),
            'seo_org_logo' => trim((string) ($s['seo_org_logo'] ?? '')),
            'seo_org_sameas' => trim((string) ($s['seo_org_sameas'] ?? '')),

            'seo_canonical_force_https' => ! empty($s['seo_canonical_force_https']),
        ]);

        Notification::make()
            ->title('SEO settings saved')
            ->body('Templates apply when a record leaves SEO title empty. Custom titles on Alternatives/Tools still win.')
            ->success()
            ->send();
    }
}
