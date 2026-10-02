<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
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

class HomepageContentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Homepage content';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.homepage-content';

    protected static ?string $title = 'Homepage content';

    protected static ?string $slug = 'homepage-content';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'canManageSystem') && $user->canManageSystem()) {
            return true;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        if (method_exists($user, 'isEditor') && $user->isEditor()) {
            return true;
        }

        if (! isset($user->role) || $user->role === null || $user->role === '') {
            return true;
        }

        return false;
    }

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        $name = config('app.name', 'Alternova');

        return [
            'home_hero_badge' => 'Open source · Self-hostable · Brandable',
            'home_hero_title_line1' => 'Find better tools.',
            'home_hero_title_line2' => 'Name them well.',
            'home_hero_subtitle' => 'Discover high-quality open-source alternatives to proprietary software, and generate brandable domain ideas with live availability checks.',
            'home_cta_finder' => 'Browse alternatives',
            'home_cta_browse' => 'Browse by category',
            'home_cta_domains' => 'Generate domains',
            'home_hubs_title' => 'Browse the catalog',
            'home_hubs_subtitle' => 'Jump in by category, language, or license — built for discovery and SEO.',
            'home_section_trending' => 'Trending',
            'home_section_popular' => 'Community favorites',
            'home_section_featured' => 'Featured alternatives',
            'home_section_recent' => 'Recently added',
            'home_section_products' => 'Browse by product',
            'home_section_products_sub' => 'Open-source options listed for each proprietary product.',
            'home_section_collections' => 'Collections',
            'home_stat_alternatives' => 'Published alternatives',
            'home_stat_tools' => 'Proprietary tools',
            'home_stat_domains' => 'Domain checks',
            'home_stat_domains_value' => 'DNS + RDAP',
            'home_stat_feeds' => 'Open feeds',
            'home_stat_feeds_value' => 'API + RSS',
            'home_editorial_enabled' => '1',
            'home_editorial_eyebrow' => 'Field guide',
            'home_editorial_title' => 'How teams actually choose open-source alternatives',
            'home_editorial_intro' => $name.' helps engineering, finance, and security teams evaluate self-hostable options with transparent code, practical signals, and domain naming tools in one place.',
            'home_editorial_body' => "What this site is for\n\nThe Open Source Alternative Finder is a curated directory that ties proprietary products people already know to open-source projects with a similar job to be done. Filter by category, language, and license, then open profiles with repository signals and self-host difficulty.\n\nThe Domain Name Idea Combinator helps founders invent brandable names, score them, and check availability without juggling registrar tabs.\n\nWhy open source enters the shortlist\n\nOpen source moves cost from subscriptions into people, monitoring, and upgrade discipline. Teams pursue it for data residency, customization, price predictability, and a credible exit path. The winning question is whether you can operate the stack safely for years.\n\nHow to evaluate\n\nCheck license fit, authentication, backups, release cadence, and whether your team already knows the primary language. Prefer clear docs and active maintainers over vanity metrics alone.",
            'home_faq_json' => json_encode([
                ['q' => 'Is every listing fully free for commercial use?', 'a' => 'No. Licenses differ. Read the upstream LICENSE and confirm it matches how you deploy software.'],
                ['q' => 'Can I suggest a missing alternative?', 'a' => 'Yes. Use Suggest a tool. Editors review submissions before publishing.'],
                ['q' => 'Do you host the software for me?', 'a' => $name.' is a discovery layer. Self-hosting or managed providers are separate decisions.'],
                ['q' => 'How often are GitHub metrics updated?', 'a' => 'On a schedule when cron is configured, and editors can refresh projects manually.'],
                ['q' => 'Is the Domain Combinator a registrar?', 'a' => 'No. It helps invent and screen names. Registration happens at registrars you choose.'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'home_checklist_title' => 'A simple decision checklist',
            'home_checklist_items' => "Problem and success metrics written down before demos begin\nLicense reviewed by someone accountable for compliance\nPilot environment isolated from production secrets\nBackup and restore tested at least once\nOwner named for upgrades and security patches\nExit plan if the project slows down or changes license",
        ];
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    public static function defaultFaqs(): array
    {
        $decoded = json_decode((string) (static::defaults()['home_faq_json'] ?? '[]'), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    public static function loadFaqs(): array
    {
        $raw = SiteSetting::get('home_faq_json', static::defaults()['home_faq_json'] ?? '[]');
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            return static::defaultFaqs();
        }

        $items = [];
        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['q'] ?? $row['question'] ?? ''));
            $a = trim((string) ($row['a'] ?? $row['answer'] ?? ''));
            if ($q === '' && $a === '') {
                continue;
            }
            $items[] = ['q' => $q, 'a' => $a];
        }

        return $items !== [] ? $items : static::defaultFaqs();
    }

    public function mount(): void
    {
        $defaults = static::defaults();
        $fill = [];
        foreach ($defaults as $key => $default) {
            if ($key === 'home_faq_json') {
                continue;
            }
            $fill[$key] = SiteSetting::get($key, $default);
        }
        $fill['home_editorial_enabled'] = SiteSetting::getBool('home_editorial_enabled', true);
        $fill['home_faqs'] = static::loadFaqs();
        $this->form->fill($fill);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Why this exists')
                    ->schema([
                        Placeholder::make('intro')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">'
                                .'Edit every visible homepage string so each install can sound unique. '
                                .'Leave a field empty to use the built-in default. '
                                .'FAQ items are simple question + answer rows — add, edit, reorder, or delete.'
                                .'</p>'
                            )),
                    ]),

                Section::make('Hero')
                    ->schema([
                        TextInput::make('home_hero_badge')->label('Badge line')->maxLength(120),
                        TextInput::make('home_hero_title_line1')->label('Title line 1')->maxLength(80),
                        TextInput::make('home_hero_title_line2')->label('Title line 2 (accent)')->maxLength(80),
                        Textarea::make('home_hero_subtitle')->label('Subtitle')->rows(3)->columnSpanFull(),
                        TextInput::make('home_cta_finder')->label('CTA: Alternatives')->maxLength(40),
                        TextInput::make('home_cta_browse')->label('CTA: Browse')->maxLength(40),
                        TextInput::make('home_cta_domains')->label('CTA: Domains')->maxLength(40),
                    ])
                    ->columns(2),

                Section::make('Section headings')
                    ->schema([
                        TextInput::make('home_hubs_title')->label('Browse hubs title')->maxLength(80),
                        TextInput::make('home_hubs_subtitle')->label('Browse hubs subtitle')->maxLength(160)->columnSpanFull(),
                        TextInput::make('home_section_trending')->label('Trending')->maxLength(60),
                        TextInput::make('home_section_popular')->label('Popular')->maxLength(60),
                        TextInput::make('home_section_featured')->label('Featured')->maxLength(60),
                        TextInput::make('home_section_recent')->label('Recently added')->maxLength(60),
                        TextInput::make('home_section_products')->label('Browse by product')->maxLength(60),
                        TextInput::make('home_section_products_sub')->label('Browse by product subtitle')->maxLength(160)->columnSpanFull(),
                        TextInput::make('home_section_collections')->label('Collections')->maxLength(60),
                    ])
                    ->columns(2),

                Section::make('Stats strip')
                    ->schema([
                        TextInput::make('home_stat_alternatives')->label('Alternatives label')->maxLength(40),
                        TextInput::make('home_stat_tools')->label('Tools label')->maxLength(40),
                        TextInput::make('home_stat_domains_value')->label('Domains value text')->maxLength(40),
                        TextInput::make('home_stat_domains')->label('Domains label')->maxLength(40),
                        TextInput::make('home_stat_feeds_value')->label('Feeds value text')->maxLength(40),
                        TextInput::make('home_stat_feeds')->label('Feeds label')->maxLength(40),
                    ])
                    ->columns(2),

                Section::make('Long-form guide (AdSense / SEO)')
                    ->schema([
                        Toggle::make('home_editorial_enabled')
                            ->label('Show long-form guide on homepage')
                            ->inline(false),
                        TextInput::make('home_editorial_eyebrow')->label('Eyebrow')->maxLength(60),
                        TextInput::make('home_editorial_title')->label('Guide title')->maxLength(120)->columnSpanFull(),
                        Textarea::make('home_editorial_intro')->label('Intro paragraph')->rows(4)->columnSpanFull(),
                        Textarea::make('home_editorial_body')
                            ->label('Body (blank line between paragraphs; a short single line can be a heading)')
                            ->rows(12)
                            ->columnSpanFull(),
                        TextInput::make('home_checklist_title')->label('Checklist title')->maxLength(80)->columnSpanFull(),
                        Textarea::make('home_checklist_items')
                            ->label('Checklist items (one per line)')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Homepage FAQ')
                    ->description('Add questions visitors ask. Click “Add FAQ item”, type the question and answer, drag to reorder. No JSON required.')
                    ->schema([
                        Repeater::make('home_faqs')
                            ->label('Questions & answers')
                            ->schema([
                                TextInput::make('q')
                                    ->label('Question')
                                    ->required()
                                    ->maxLength(200)
                                    ->columnSpanFull(),
                                Textarea::make('a')
                                    ->label('Answer')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add FAQ item')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? 'New question')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $faqs = [];
        foreach ($data['home_faqs'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $q = trim((string) ($row['q'] ?? ''));
            $a = trim((string) ($row['a'] ?? ''));
            if ($q === '') {
                continue;
            }
            $faqs[] = ['q' => $q, 'a' => $a];
        }

        unset($data['home_faqs']);
        $data['home_faq_json'] = json_encode($faqs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $data['home_editorial_enabled'] = ! empty($data['home_editorial_enabled']);

        SiteSetting::setMany($data);

        Notification::make()
            ->title('Homepage content saved')
            ->body(count($faqs).' FAQ item(s) stored.')
            ->success()
            ->send();
    }
}
