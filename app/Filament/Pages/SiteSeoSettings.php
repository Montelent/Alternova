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
            'seo_private_robots' => $g('seo_private_robots', 'noindex,nofollow'),
            'seo_site_noindex' => SiteSetting::getBool('seo_site_noindex', false),

            'seo_title_home' => $g('seo_title_home', '%sitename% %sep% Open-source alternatives & brandable domains'),
            'seo_desc_home' => $g('seo_desc_home', ''),
            'seo_title_alternative' => $g('seo_title_alternative', '%title% Open-Source %prop% Alternative %sep% %sitename%'),
            'seo_desc_alternative' => $g('seo_desc_alternative', '%title% is a free, self-hostable open-source alternative to %prop%. %excerpt%'),
            'seo_title_tool' => $g('seo_title_tool', '%count% Open Source Alternatives to %title% %sep% %sitename%'),
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
            'seo_sitemap_domains' => SiteSetting::getBool('seo_sitemap_domains', true),
            'seo_sitemap_compare' => SiteSetting::getBool('seo_sitemap_compare', false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('How templates work')
                    ->description('Like Rank Math / Yoast. Variables are replaced when a page has no custom SEO title on the record.')
                    ->schema([
                        Placeholder::make('token_guide')
                            ->label('Variables')
                            ->content(new \Illuminate\Support\HtmlString(<<<'HTML'
<div class="text-sm space-y-2 text-gray-600 dark:text-gray-300">
  <p><code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%sitename%</code> site name ·
  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%sep%</code> separator ·
  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%title%</code> record name ·
  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%prop%</code> proprietary product ·
  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%count%</code> number of alternatives (tool pages) ·
  <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 rounded">%excerpt%</code> short description</p>
  <p><strong>Tool title example:</strong> <code class="text-xs">%count% Open Source Alternatives to %title% %sep% %sitename%</code><br>
  → <em>5 Open Source Alternatives to Notion | Alternova</em></p>
</div>
HTML)),
                    ]),

                Section::make('1. General')
                    ->schema([
                        TextInput::make('seo_site_name')->label('Site name')->required()->maxLength(80),
                        TextInput::make('site_tagline')->label('Tagline')->maxLength(120),
                        TextInput::make('seo_separator')->label('Title separator')->maxLength(5),
                        Textarea::make('default_meta_description')
                            ->label('Fallback meta description')
                            ->rows(3)
                            ->maxLength(160)
                            ->columnSpanFull(),
                        TextInput::make('seo_robots_default')
                            ->label('Default robots (public pages)')
                            ->helperText('Recommended: index,follow,max-image-preview:large,max-snippet:-1')
                            ->columnSpanFull(),
                        TextInput::make('seo_private_robots')
                            ->label('Robots for private pages (login, account, favorites…)')
                            ->helperText('Recommended: noindex,nofollow')
                            ->columnSpanFull(),
                        Toggle::make('seo_site_noindex')
                            ->label('Noindex entire public site (staging)')
                            ->helperText('Turn ON only for staging or pre-launch. Overrides every page.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('2. Title & meta templates')
                    ->schema([
                        TextInput::make('seo_title_home')->label('Homepage title')->columnSpanFull(),
                        Textarea::make('seo_desc_home')->label('Homepage description')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_alternative')->label('Alternative title template')->columnSpanFull(),
                        Textarea::make('seo_desc_alternative')->label('Alternative description template')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_tool')->label('Proprietary / alternativesto title')
                            ->helperText('Use %count% for the number of listed alternatives.')
                            ->columnSpanFull(),
                        Textarea::make('seo_desc_tool')->label('Proprietary description template')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_finder')->label('Finder title'),
                        TextInput::make('seo_title_domains')->label('Domains title'),
                        TextInput::make('seo_title_compare')->label('Compare title'),
                    ])->columns(2),

                Section::make('3. Sitemap controls')
                    ->description('Canonical proprietary URLs are always /alternativesto/{slug}. Pages marked noindex on the record are omitted.')
                    ->schema([
                        Toggle::make('seo_sitemap_domains')
                            ->label('Include /domains in sitemap')
                            ->inline(false),
                        Toggle::make('seo_sitemap_compare')
                            ->label('Include /alternatives/compare in sitemap')
                            ->helperText('Usually leave OFF (thin or parameter-heavy page).')
                            ->inline(false),
                    ])->columns(1),

                Section::make('4. Social (Open Graph & X)')
                    ->schema([
                        TextInput::make('og_image_url')->label('Default social image URL')->url()->columnSpanFull(),
                        TextInput::make('twitter_handle')->label('X / Twitter handle')->placeholder('@alternova'),
                        Select::make('twitter_card')->label('Twitter card type')->options([
                            'summary_large_image' => 'Summary with large image',
                            'summary' => 'Summary',
                        ]),
                        TextInput::make('seo_facebook_app_id')->label('Facebook App ID')->maxLength(40),
                    ])->columns(2),

                Section::make('5. Webmaster verification')
                    ->schema([
                        TextInput::make('seo_verify_google')->label('Google Search Console'),
                        TextInput::make('seo_verify_bing')->label('Bing Webmaster'),
                        TextInput::make('seo_verify_yandex')->label('Yandex'),
                        TextInput::make('seo_verify_pinterest')->label('Pinterest'),
                    ])->columns(2)->collapsed(),

                Section::make('6. Organization schema')
                    ->schema([
                        TextInput::make('seo_org_name')->label('Organization name'),
                        TextInput::make('seo_org_logo')->label('Logo URL')->url()->columnSpanFull(),
                        Textarea::make('seo_org_sameas')
                            ->label('Social profile URLs (one per line)')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),

                Section::make('7. Advanced')
                    ->schema([
                        Toggle::make('seo_canonical_force_https')
                            ->label('Prefer HTTPS for defaults')
                            ->helperText('Keep ON in production.'),
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
            'seo_private_robots' => trim((string) ($s['seo_private_robots'] ?? 'noindex,nofollow')),
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
            'seo_sitemap_domains' => ! empty($s['seo_sitemap_domains']),
            'seo_sitemap_compare' => ! empty($s['seo_sitemap_compare']),
        ]);

        Notification::make()
            ->title('SEO settings saved')
            ->body('Sitemap and robots rules apply on the next request. Clear view cache if pages look stale.')
            ->success()
            ->send();
    }
}
