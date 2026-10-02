<?php

namespace App\Filament\Forms;

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
                ->description('How this '.$entityLabel.' may look in Google. Leave meta fields empty to auto-fill from the '.$bodyField.' / site templates.')
                ->icon('heroicon-o-magnifying-glass')
                ->schema([
                    ViewField::make('serp_preview')
                        ->label('Snippet preview')
                        ->view('filament.forms.seo-serp-preview')
                        ->viewData(fn (Get $get) => [
                            'previewTitle' => static::previewTitle($get, $nameField),
                            'previewDesc' => static::previewDescription($get, $bodyField),
                            'previewSlug' => (string) ($get($slugField) ?: 'your-slug'),
                            'siteName' => config('app.name', 'Alternova'),
                            'siteOrigin' => rtrim((string) config('app.url'), '/') ?: '',
                        ])
                        ->columnSpanFull(),

                    TextInput::make('meta_title')
                        ->label('SEO title')
                        ->maxLength(70)
                        ->live(debounce: 300)
                        ->helperText(fn (Get $get) => static::titleHelp((string) $get('meta_title')))
                        ->placeholder('Leave empty → auto from name + site template')
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
                        ->content(function (Get $get) use ($slugField, $nameField, $bodyField) {
                            $kw = trim((string) $get('focus_keyword'));
                            if ($kw === '') {
                                return 'Add a focus keyphrase to see simple checks.';
                            }

                            $title = strtolower((string) ($get('meta_title') ?: $get($nameField)));
                            $desc = strtolower(static::previewDescription($get, $bodyField));
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

    public static function previewTitle(Get $get, string $nameField = 'name'): string
    {
        $meta = trim((string) $get('meta_title'));
        if ($meta !== '') {
            return $meta;
        }

        $name = trim((string) $get($nameField));
        $site = config('app.name', 'Alternova');

        return $name !== '' ? $name.' | '.$site : $site;
    }

    public static function previewDescription(Get $get, string $bodyField = 'description'): string
    {
        $meta = trim((string) $get('meta_description'));
        if ($meta !== '') {
            return Str::limit($meta, 160);
        }

        $body = static::plainText((string) $get($bodyField));
        if ($body !== '') {
            return Str::limit($body, 155);
        }

        $excerpt = static::plainText((string) $get('excerpt'));
        if ($excerpt !== '') {
            return Str::limit($excerpt, 155);
        }

        return 'Meta description will be auto-generated from the main content when this is left empty.';
    }

    public static function plainText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    protected static function titleHelp(string $value): string
    {
        $len = mb_strlen($value);
        if ($len === 0) {
            return 'Empty → auto from name + site name. Ideal length: 50–60 characters.';
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
