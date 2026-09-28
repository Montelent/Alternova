<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;

/**
 * Yoast / Rank Math style SEO fields shared by Alternatives and Tools.
 */
class SeoForm
{
    /**
     * @param  string  $entityLabel  e.g. "alternative" or "tool"
     * @param  string  $defaultPath  route name hint for preview URL
     * @return array<int, \Filament\Forms\Components\Component>
     */
    public static function schema(string $entityLabel = 'page', string $slugField = 'slug'): array
    {
        return [
            Section::make('Search appearance (Google snippet)')
                ->description('How this '.$entityLabel.' may look in Google. Leave blank to use global SEO templates from System → SEO settings.')
                ->icon('heroicon-o-magnifying-glass')
                ->schema([
                    ViewField::make('serp_preview')
                        ->label('Snippet preview')
                        ->view('filament.forms.seo-serp-preview')
                        ->columnSpanFull(),

                    TextInput::make('meta_title')
                        ->label('SEO title')
                        ->maxLength(70)
                        ->live(onBlur: true)
                        ->helperText(fn (Get $get) => static::titleHelp((string) $get('meta_title')))
                        ->placeholder('Leave empty to auto-generate from template')
                        ->columnSpanFull(),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160)
                        ->live(onBlur: true)
                        ->helperText(fn (Get $get) => static::descHelp((string) $get('meta_description')))
                        ->placeholder('Leave empty to auto-generate from template')
                        ->columnSpanFull(),

                    TextInput::make('focus_keyword')
                        ->label('Focus keyphrase')
                        ->maxLength(120)
                        ->helperText('Primary phrase you want to rank for (e.g. “notion open source alternative”). Used for editorial guidance only.')
                        ->columnSpanFull(),

                    Placeholder::make('keyword_check')
                        ->label('Keyphrase check')
                        ->content(function (Get $get) {
                            $kw = trim((string) $get('focus_keyword'));
                            if ($kw === '') {
                                return 'Add a focus keyphrase to see simple checks.';
                            }
                            $title = strtolower((string) $get('meta_title'));
                            $desc = strtolower((string) $get('meta_description'));
                            $name = strtolower((string) $get('name'));
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
                        ->placeholder('Defaults to meta description')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsed(),
        ];
    }

    protected static function titleHelp(string $value): string
    {
        $len = mb_strlen($value);
        if ($len === 0) {
            return 'Empty → global template applies. Ideal filled length: 50–60 characters.';
        }
        $status = $len <= 60 ? 'Good length' : ($len <= 70 ? 'Slightly long' : 'Too long — may truncate in Google');

        return "{$len}/60 characters · {$status}";
    }

    protected static function descHelp(string $value): string
    {
        $len = mb_strlen($value);
        if ($len === 0) {
            return 'Empty → global template applies. Ideal filled length: 120–155 characters.';
        }
        $status = $len <= 155 ? 'Good length' : ($len <= 160 ? 'Slightly long' : 'Too long — may truncate');

        return "{$len}/155 characters · {$status}";
    }
}
