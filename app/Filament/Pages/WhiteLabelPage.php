<?php

namespace App\Filament\Pages;

use App\Filament\Forms\ImageField;
use App\Models\SiteSetting;
use App\Support\WhiteLabelSettings;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class WhiteLabelPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationLabel = 'White label';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.white-label';

    protected static ?string $title = 'White label / branding';

    protected static ?string $slug = 'white-label';

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
        $d = WhiteLabelSettings::defaults();
        $fill = [];
        foreach ($d as $key => $default) {
            $fill[$key] = SiteSetting::get($key, $default);
        }
        $this->form->fill($fill);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('1. Site identity')
                    ->description('Name and short line visitors see in the header and browser tab.')
                    ->schema([
                        TextInput::make('brand_site_name')
                            ->label('Site name')
                            ->required()
                            ->maxLength(80)
                            ->helperText('Example: OpenAlt Hub, ToolFinder, your brand name.'),
                        TextInput::make('brand_letter')
                            ->label('Logo letter (when no image)')
                            ->maxLength(2)
                            ->helperText('One letter inside the colored square if you have not uploaded a logo yet.'),
                        TextInput::make('brand_tagline')
                            ->label('Tagline')
                            ->maxLength(160)
                            ->columnSpanFull()
                            ->helperText('Shown under the name in the footer and SEO defaults.'),
                        TextInput::make('brand_admin_name')
                            ->label('Admin panel title')
                            ->maxLength(80)
                            ->helperText('Shown in the Filament admin sidebar header.'),
                        TextInput::make('brand_support_email')
                            ->label('Public support email (optional)')
                            ->email()
                            ->maxLength(120),
                    ])
                    ->columns(2),

                Section::make('2. Logo (direct upload or remote URL)')
                    ->description('Upload a file from your computer, or paste a URL. You can also import a remote URL into permanent storage on this server.')
                    ->schema([
                        ...ImageField::make('brand_logo_path', 'Logo image (upload)', 'branding', true, 'brand_logo_url'),
                        Placeholder::make('logo_hint')
                            ->content(new HtmlString(
                                '<p class="text-sm text-gray-600 dark:text-gray-300">'
                                .'Preferred: square PNG or SVG, at least 128×128. Upload is saved under <code>/uploads/branding/</code>. '
                                .'Remote URL works without uploading; use “Import URL to server” if you want a local copy.'
                                .'</p>'
                            )),
                    ]),

                Section::make('3. Favicon')
                    ->description('Small icon in the browser tab. Same upload + URL options.')
                    ->schema([
                        ...ImageField::make('brand_favicon_path', 'Favicon (upload)', 'branding', true, 'brand_favicon_url'),
                    ]),

                Section::make('4. Brand color')
                    ->schema([
                        TextInput::make('brand_primary')
                            ->label('Primary color (hex)')
                            ->placeholder('#4f46e5')
                            ->maxLength(7)
                            ->helperText('Example: #4f46e5 or #0ea5e9. Used for buttons, links, and the admin theme.')
                            ->prefixIcon('heroicon-o-swatch'),
                        Textarea::make('brand_footer_text')
                            ->label('Custom footer copyright line (optional)')
                            ->rows(2)
                            ->helperText('Leave empty to use: © YEAR Site name')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $hex = trim((string) ($data['brand_primary'] ?? '#4f46e5'));
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            Notification::make()->title('Primary color must look like #4f46e5')->danger()->send();

            return;
        }

        // Normalize FileUpload array → path string
        foreach (['brand_logo_path', 'brand_favicon_path'] as $key) {
            $v = $data[$key] ?? null;
            if (is_array($v)) {
                $data[$key] = array_values($v)[0] ?? '';
            }
            $data[$key] = is_string($data[$key] ?? null) ? $data[$key] : '';
        }

        $pairs = [];
        foreach (array_keys(WhiteLabelSettings::defaults()) as $key) {
            $pairs[$key] = $data[$key] ?? '';
        }
        $pairs['brand_primary'] = strtolower($hex);

        SiteSetting::setMany($pairs);

        // Keep SEO site name / tagline in sync for buyers who only edit white-label
        SiteSetting::set('seo_site_name', WhiteLabelSettings::siteName());
        SiteSetting::set('site_tagline', WhiteLabelSettings::tagline());

        Notification::make()
            ->title('Branding saved')
            ->body('Frontend and admin will use the new name, logo, and color after a refresh.')
            ->success()
            ->send();
    }

    public function importLogoFromUrl(): void
    {
        $url = trim((string) ($this->data['brand_logo_url'] ?? ''));
        if ($url === '') {
            Notification::make()->title('Paste a logo URL first')->warning()->send();

            return;
        }

        $path = WhiteLabelSettings::importRemoteImage($url, 'logo');
        if (! $path) {
            Notification::make()->title('Could not download that URL')->danger()->send();

            return;
        }

        $this->data['brand_logo_path'] = $path;
        SiteSetting::set('brand_logo_path', $path);
        Notification::make()->title('Logo imported to /uploads/'.$path)->success()->send();
    }

    public function importFaviconFromUrl(): void
    {
        $url = trim((string) ($this->data['brand_favicon_url'] ?? ''));
        if ($url === '') {
            Notification::make()->title('Paste a favicon URL first')->warning()->send();

            return;
        }

        $path = WhiteLabelSettings::importRemoteImage($url, 'favicon');
        if (! $path) {
            Notification::make()->title('Could not download that URL')->danger()->send();

            return;
        }

        $this->data['brand_favicon_path'] = $path;
        SiteSetting::set('brand_favicon_path', $path);
        Notification::make()->title('Favicon imported to /uploads/'.$path)->success()->send();
    }
}
