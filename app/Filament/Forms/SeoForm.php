<?php

namespace App\Filament\Forms;

use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Services\SeoManager;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Illuminate\Support\Str;

/**
 * Rank Math / Yoast style SEO fields for tools, alternatives, and pages.
 */
class SeoForm
{
    /**
     * @param  string  $entityLabel  human label ("proprietary tool", "page", …)
     * @param  string  $slugField  form field used for the public slug
     * @param  string  $nameField  form field used for the display name/title
     * @param  string  $bodyField  form field used as meta-description fallback (description, body_html, excerpt…)
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(
        string $entityLabel = 'page',
        string $slugField = 'slug',
        string $nameField = 'name',
        string $bodyField = 'description',
    ): array {
        return [
            Section::make('Search appearance (Google snippet)')
                ->description('How this '.$entityLabel.' may look in Google. Matches the live page title template when SEO title is empty.')
                ->icon('heroicon-o-magnifying-glass')
                ->schema([
                    ViewField::make('serp_preview')
                        ->label('Snippet preview')
                        ->view('filament.forms.seo-serp-preview')
                        ->viewData(fn (Get $get) => [
                            'previewTitle' => static::previewTitle($get, $nameField, $entityLabel),
                            'previewDesc' => static::previewDescription($get, $bodyField, $nameField, $entityLabel),
                            'previewSlug' => (string) ($get($slugField) ?: 'your-slug'),
                            'siteName' => app(SeoManager::class)->siteName(),
                            'siteOrigin' => rtrim((string) config('app.url'), '/') ?: '',
                        ])
                        ->columnSpanFull(),

                    TextInput::make('meta_title')
                        ->label('SEO title')
                        ->maxLength(70)
                        ->live(debounce: 300)
                        ->helperText(fn (Get $get) => static::titleHelp((string) $get('meta_title'), $entityLabel))
                        ->placeholder('Leave empty → same auto title as the live public page')
                        ->columnSpanFull(),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160)
                        ->live(debounce: 300)
                        ->helperText(fn (Get $get) => static::descHelp((string) $get('meta_description'), $get($bodyField)))
                        ->placeholder('Leave empty → auto from the main Description / body')
                        ->columnSpanFull(),

                    TextInput::make('focus_keyword')
                        ->label('Focus keyphrase')
                        ->maxLength(120)
                        ->live(debounce: 300)
                        ->helperText('Primary phrase you want to rank for. Editorial guidance only.')
                        ->columnSpanFull(),

                    Placeholder::make('keyword_check')
                        ->label('Keyphrase check')
                        ->content(function (Get $get) use ($slugField, $nameField, $bodyField, $entityLabel) {
                            $kw = trim((string) $get('focus_keyword'));
                            if ($kw === '') {
                                return 'Add a focus keyphrase to see simple checks.';
                            }

                            $title = strtolower(static::previewTitle($get, $nameField, $entityLabel));
                            $desc = strtolower(static::previewDescription($get, $bodyField, $nameField, $entityLabel));
                            $name = strtolower((string) $get($nameField));
                            $slug = strtolower((string) $get($slugField));
                            $needle = strtolower($kw);

                            $bits = [];
                            $bits[] = str_contains($title, $needle) || str_contains($name, $needle)
                                ? '✓ Keyphrase appears in title/name'
                                : '✗ Keyphrase missing from SEO title';
                            $bits[] = str_contains($desc, $needle)
                                ? '✓ Keyphrase appears in meta description'
                                : '○ Keyphrase not in meta description (optional)';
                            $bits[] = str_contains($slug, str_replace(' ', '-', $needle))
                                || str_contains($slug, str_replace(' ', '', $needle))
                                ? '✓ Keyphrase reflected in slug'
                                : '○ Slug does not include keyphrase (optional)';

                            return implode(' · ', $bits);
                        })
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsed(false),

            Section::make('Robots & canonical')
                ->description('Control indexing for this '.$entityLabel.' only. Global “noindex site” in SEO settings still wins on staging.')
                ->icon('heroicon-o-cog-6-tooth')
                ->schema([
                    Select::make('robots_meta')
                        ->label('Robots meta')
                        ->options([
                            '' => 'Default (use site setting)',
                            'index,follow' => 'Index + follow',
                            'index,nofollow' => 'Index + nofollow',
                            'noindex,follow' => 'Noindex + follow',
                            'noindex,nofollow' => 'Noindex + nofollow',
                        ])
                        ->native(false),
                    TextInput::make('canonical_url')
                        ->label('Canonical URL override')
                        ->url()
                        ->helperText('Usually leave empty. Set only if this content is a copy of another URL.')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsed(),

            Section::make('Social share (Open Graph)')
                ->description('Facebook, LinkedIn, Slack, etc. Falls back to SEO title/description and the site default OG image.')
                ->icon('heroicon-o-share')
                ->schema([
                    TextInput::make('og_title')
                        ->label('Social title')
                        ->maxLength(120)
                        ->live(debounce: 300)
                        ->placeholder('Defaults to SEO title'),
                    TextInput::make('og_image_url')
                        ->label('Social image URL')
                        ->url()
                        ->helperText('Absolute HTTPS URL. Ideal 1200×630.')
                        ->columnSpanFull(),
                    Textarea::make('og_description')
                        ->label('Social description')
                        ->rows(2)
                        ->maxLength(200)
                        ->live(debounce: 300)
                        ->placeholder('Defaults to meta description')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsed(),
        ];
    }

    public static function previewTitle(Get $get, string $nameField = 'name', string $entityLabel = 'page'): string
    {
        $meta = trim((string) $get('meta_title'));
        if ($meta !== '') {
            return $meta;
        }

        $seo = app(SeoManager::class);
        $name = trim((string) ($get($nameField) ?: $get('title') ?: ''));
        $label = strtolower($entityLabel);

        if (str_contains($label, 'alternative')) {
            $tpl = SiteSetting::get(
                'seo_title_alternative',
                '%title% Open-Source %prop% Alternative %sep% %sitename%'
            );

            return $seo->replace((string) $tpl, [
                '%title%' => $name !== '' ? $name : 'Alternative',
                '%prop%' => static::resolveProprietaryName($get) ?: 'proprietary tools',
                '%license%' => trim((string) ($get('license_type') ?? '')),
                '%language%' => trim((string) ($get('primary_language') ?? '')),
                '%health%' => (string) round((float) ($get('overall_health_score') ?? 0)),
            ]);
        }

        if (str_contains($label, 'proprietary') || str_contains($label, 'tool')) {
            $tpl = SiteSetting::get(
                'seo_title_tool',
                '%count% Open Source Alternatives to %title% %sep% %sitename%'
            );

            return $seo->replace((string) $tpl, [
                '%title%' => $name !== '' ? $name : 'Tool',
                '%count%' => '',
            ]);
        }

        return $seo->replace('%page% %sep% %sitename%', [
            '%page%' => $name !== '' ? $name : 'Page',
        ]);
    }

    public static function previewDescription(
        Get $get,
        string $bodyField = 'description',
        string $nameField = 'name',
        string $entityLabel = 'page',
    ): string {
        $meta = trim((string) $get('meta_description'));
        if ($meta !== '') {
            return Str::limit($meta, 160);
        }

        $seo = app(SeoManager::class);
        $name = trim((string) ($get($nameField) ?: $get('title') ?: ''));
        $label = strtolower($entityLabel);
        $excerpt = static::plainText((string) $get($bodyField));

        if (str_contains($label, 'alternative')) {
            $tpl = SiteSetting::get('seo_desc_alternative', '');
            $prop = static::resolveProprietaryName($get) ?: 'proprietary software';

            if ($tpl) {
                return Str::limit($seo->replace((string) $tpl, [
                    '%title%' => $name,
                    '%prop%' => $prop,
                    '%license%' => trim((string) ($get('license_type') ?? '')) ?: 'open-source',
                    '%language%' => trim((string) ($get('primary_language') ?? '')),
                    '%health%' => (string) round((float) ($get('overall_health_score') ?? 0)),
                    '%excerpt%' => Str::limit($excerpt, 120),
                ]), 160);
            }

            if ($excerpt !== '') {
                return Str::limit($excerpt, 155);
            }

            return Str::limit(
                ($name !== '' ? $name : 'This project').' is a free, self-hostable open-source alternative to '.$prop.'.',
                155
            );
        }

        if (str_contains($label, 'proprietary') || str_contains($label, 'tool')) {
            $tpl = SiteSetting::get('seo_desc_tool', '');

            if ($tpl) {
                return Str::limit($seo->replace((string) $tpl, [
                    '%title%' => $name,
                    '%excerpt%' => Str::limit($excerpt, 120),
                ]), 160);
            }

            if ($excerpt !== '') {
                return Str::limit($excerpt, 155);
            }

            return Str::limit(
                'Browse free, self-hostable open-source alternatives to '.($name !== '' ? $name : 'this tool').'.',
                155
            );
        }

        if ($excerpt !== '') {
            return Str::limit($excerpt, 155);
        }

        $excerptField = static::plainText((string) $get('excerpt'));
        if ($excerptField !== '') {
            return Str::limit($excerptField, 155);
        }

        return 'Meta description will be auto-generated from the main content when this is left empty.';
    }

    /**
     * All selected proprietary tools (multi-select), joined for %prop%.
     */
    protected static function resolveProprietaryName(Get $get): string
    {
        $ids = [];
        $tools = $get('proprietaryTools');
        if (is_array($tools) && $tools !== []) {
            $ids = array_values(array_filter($tools, fn ($id) => filled($id)));
        }
        if ($ids === []) {
            $single = $get('proprietary_tool_id');
            if (filled($single)) {
                $ids = [$single];
            }
        }
        if ($ids === []) {
            return '';
        }

        try {
            $byId = ProprietaryTool::query()->whereIn('id', $ids)->get()->keyBy('id');
            $ordered = collect($ids)->map(fn ($id) => $byId->get($id)?->name)->filter()->values();

            return app(SeoManager::class)->joinNames($ordered);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function plainText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    protected static function titleHelp(string $value, string $entityLabel = 'page'): string
    {
        $len = mb_strlen($value);
        if ($len === 0) {
            return 'Empty → uses the same auto title as the live page (site SEO templates). Ideal custom length: 50–60 characters.';
        }
        $status = $len <= 60 ? 'Good length' : ($len <= 70 ? 'Slightly long' : 'Too long — may truncate in Google');

        return "{$len}/60 characters · {$status}";
    }

    protected static function descHelp(string $value, mixed $body = null): string
    {
        $len = mb_strlen($value);
        if ($len === 0) {
            $plain = static::plainText(is_string($body) ? $body : '');
            if ($plain !== '') {
                return 'Empty → will use the main Description ('.mb_strlen(Str::limit($plain, 155)).' chars preview). Ideal: 120–155.';
            }

            return 'Empty → auto from Description when available. Ideal filled length: 120–155 characters.';
        }
        $status = $len <= 155 ? 'Good length' : ($len <= 160 ? 'Slightly long' : 'Too long — may truncate');

        return "{$len}/155 characters · {$status}";
    }
}
