<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
            // General
            'seo_site_name' => $g('seo_site_name', config('app.name', 'Alternova')),
            'site_tagline' => $g('site_tagline', 'Open-source alternatives & brandable domains'),
            'seo_separator' => $g('seo_separator', '|'),
            'default_meta_description' => $g(
                'default_meta_description',
                'Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.'
            ),
            'seo_robots_default' => $g('seo_robots_default', 'index,follow,max-image-preview:large,max-snippet:-1'),
            'seo_site_noindex' => SiteSetting::getBool('seo_site_noindex', false),

            // Titles
            'seo_title_home' => $g('seo_title_home', '%sitename% %sep% Open-source alternatives & brandable domains'),
            'seo_desc_home' => $g('seo_desc_home', ''),
            'seo_title_alternative' => $g('seo_title_alternative', '%title% — Open-Source %prop% Alternative %sep% %sitename%'),
            'seo_desc_alternative' => $g('seo_desc_alternative', '%title% is a free, self-hostable open-source alternative to %prop%. %excerpt%'),
            'seo_title_tool' => $g('seo_title_tool', 'Open-source alternatives to %title% %sep% %sitename%'),
            'seo_desc_tool' => $g('seo_desc_tool', 'Browse free, self-hostable open-source alternatives to %title%. %excerpt%'),
            'seo_title_finder' => $g('seo_title_finder', 'Open Source Alternatives Finder %sep% %sitename%'),
            'seo_title_domains' => $g('seo_title_domains', 'Domain Name Idea Combinator %sep% %sitename%'),
            'seo_title_compare' => $g('seo_title_compare', 'Compare open-source alternatives %sep% %sitename%'),

            // Social
            'twitter_handle' => $g('twitter_handle', ''),
            'twitter_card' => $g('twitter_card', 'summary_large_image'),
            'og_image_url' => $g('og_image_url', ''),
            'seo_facebook_app_id' => $g('seo_facebook_app_id', ''),

            // Webmaster
            'seo_verify_google' => $g('seo_verify_google', ''),
            'seo_verify_bing' => $g('seo_verify_bing', ''),
            'seo_verify_yandex' => $g('seo_verify_yandex', ''),
            'seo_verify_pinterest' => $g('seo_verify_pinterest', ''),

            // Schema
            'seo_org_name' => $g('seo_org_name', config('app.name', 'Alternova')),
            'seo_org_logo' => $g('seo_org_logo', ''),
            'seo_org_sameas' => $g('seo_org_sameas', ''),

            // Advanced
            'seo_canonical_force_https' => SiteSetting::getBool('seo_canonical_force_https', true),
            'seo_remove_category_base' => SiteSetting::getBool('seo_remove_category_base', false),
        ]);
    }

    public function form(Form $form): Form
    {
        $tokenHelp = 'Tokens: %sitename% %sep% %tagline% %title% %prop% %license% %language% %health% %excerpt% %page%';

        return $form
            ->schema([
                Section::make('General')
                    ->description('Like Rank Math / Yoast “General” tab — site identity and defaults.')
                    ->schema([
                        TextInput::make('seo_site_name')->label('Site name')->required()->maxLength(80),
                        TextInput::make('site_tagline')->label('Tagline')->maxLength(120),
                        TextInput::make('seo_separator')
                            ->label('Title separator')
                            ->maxLength(5)
                            ->helperText('Common: | – · >'),
                        Textarea::make('default_meta_description')
                            ->label('Fallback meta description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Used when a page has no description (~150–160 chars).'),
                        TextInput::make('seo_robots_default')
                            ->label('Default robots meta')
                            ->helperText('Example: index,follow,max-image-preview:large,max-snippet:-1'),
                        Toggle::make('seo_site_noindex')
                            ->label('Discourage search engines (noindex entire site)')
                            ->helperText('Use on staging. Overrides all public pages.'),
                    ])->columns(2),

                Section::make('Title & meta templates')
                    ->description('Templates for automatic titles when a record has no custom meta title.')
                    ->schema([
                        TextInput::make('seo_title_home')->label('Homepage title')->columnSpanFull()->helperText($tokenHelp),
                        Textarea::make('seo_desc_home')->label('Homepage description override')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_alternative')->label('Alternative title template')->columnSpanFull(),
                        Textarea::make('seo_desc_alternative')->label('Alternative description template')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_tool')->label('Proprietary tool title template')->columnSpanFull(),
                        Textarea::make('seo_desc_tool')->label('Proprietary tool description template')->rows(2)->columnSpanFull(),
                        TextInput::make('seo_title_finder')->label('Finder page title'),
                        TextInput::make('seo_title_domains')->label('Domains page title'),
                        TextInput::make('seo_title_compare')->label('Compare page title'),
                    ])->columns(2)->collapsed(false),

                Section::make('Social (Open Graph & X)')
                    ->description('Default share cards when a page has no custom image.')
                    ->schema([
                        TextInput::make('og_image_url')
                            ->label('Default OG image URL')
                            ->url()
                            ->helperText('1200×630 recommended. Absolute HTTPS URL.')
                            ->columnSpanFull(),
                        TextInput::make('twitter_handle')->label('X / Twitter @handle')->placeholder('@alternova'),
                        Select::make('twitter_card')
                            ->label('Twitter card type')
                            ->options([
                                'summary_large_image' => 'Summary large image',
                                'summary' => 'Summary',
                            ]),
                        TextInput::make('seo_facebook_app_id')->label('Facebook App ID')->maxLength(40),
                    ])->columns(2),

                Section::make('Webmaster verification')
                    ->description('Paste meta verification content values (not full HTML tags).')
                    ->schema([
                        TextInput::make('seo_verify_google')->label('Google Search Console')->placeholder('content value only'),
                        TextInput::make('seo_verify_bing')->label('Bing Webmaster')->placeholder('content value only'),
                        TextInput::make('seo_verify_yandex')->label('Yandex')->placeholder('content value only'),
                        TextInput::make('seo_verify_pinterest')->label('Pinterest')->placeholder('content value only'),
                    ])->columns(2)->collapsed(),

                Section::make('Schema / Organization')
                    ->description('Used in homepage JSON-LD and Knowledge Graph hints.')
                    ->schema([
                        TextInput::make('seo_org_name')->label('Organization name'),
                        TextInput::make('seo_org_logo')->label('Organization logo URL')->url()->columnSpanFull(),
                        Textarea::make('seo_org_sameas')
                            ->label('sameAs social profile URLs')
                            ->rows(4)
                            ->helperText('One URL per line (X, LinkedIn, GitHub, etc.)')
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),

                Section::make('Advanced')
                    ->schema([
                        Toggle::make('seo_canonical_force_https')
                            ->label('Prefer HTTPS in canonicals (when app URL is https)'),
                        Toggle::make('seo_remove_category_base')
                            ->label('Reserved (category base)')
                            ->disabled()
                            ->helperText('Placeholder for future taxonomy URLs.'),
                    ])->columns(2)->collapsed(),
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
            'seo_remove_category_base' => ! empty($s['seo_remove_category_base']),
        ]);

        Notification::make()
            ->title('SEO settings saved')
            ->body('Templates and social defaults apply on the next page load.')
            ->success()
            ->send();
    }
}
