<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\OpenSourceAlternative;
use App\Models\Page;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

class SeoManager
{
    public function separator(): string
    {
        return SiteSetting::get('seo_separator', '|') ?: '|';
    }

    public function siteName(): string
    {
        return SiteSetting::get('seo_site_name', config('app.name', 'Alternova')) ?: 'Alternova';
    }

    public function tagline(): string
    {
        return (string) SiteSetting::get('site_tagline', 'Open-source alternatives & brandable domains');
    }

    public function defaultDescription(): string
    {
        return (string) SiteSetting::get(
            'default_meta_description',
            'Discover self-hostable open-source alternatives to proprietary tools.'
        );
    }

    public function defaultOgImage(): ?string
    {
        $url = trim((string) SiteSetting::get('og_image_url', ''));

        return $url !== '' ? $url : null;
    }

    public function twitterHandle(): ?string
    {
        $h = trim((string) SiteSetting::get('twitter_handle', ''));
        if ($h === '') {
            return null;
        }

        return str_starts_with($h, '@') ? $h : '@'.$h;
    }

    public function twitterCard(): string
    {
        return SiteSetting::get('twitter_card', 'summary_large_image') ?: 'summary_large_image';
    }

    public function siteNoIndex(): bool
    {
        return SiteSetting::getBool('seo_site_noindex', false);
    }

    public function plainText(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    public function replace(string $template, array $vars = []): string
    {
        $defaults = [
            '%sitename%' => $this->siteName(),
            '%sep%' => $this->separator(),
            '%tagline%' => $this->tagline(),
            '%page%' => '',
            '%title%' => '',
            '%prop%' => '',
            '%license%' => '',
            '%language%' => '',
            '%health%' => '',
            '%count%' => '',
            '%excerpt%' => '',
        ];

        $map = array_merge($defaults, $vars);
        $out = str_replace(array_keys($map), array_values($map), $template);

        return trim(preg_replace('/\s+/', ' ', $out) ?? $out);
    }

    public function homepageTitle(): string
    {
        $tpl = SiteSetting::get(
            'seo_title_home',
            '%sitename% %sep% Open-source alternatives & brandable domains'
        );

        return $this->replace((string) $tpl);
    }

    public function homepageDescription(): string
    {
        $tpl = SiteSetting::get('seo_desc_home', '');

        return $tpl !== '' && $tpl !== null
            ? $this->replace((string) $tpl)
            : $this->defaultDescription();
    }

    /**
     * All linked proprietary tool names for %prop% (multi-select).
     * e.g. Notion | Notion and Slack | Notion, Slack and Teams
     */
    public function proprietaryNames(OpenSourceAlternative $alt): string
    {
        $names = collect();

        try {
            if ($alt->relationLoaded('proprietaryTools')) {
                $names = $alt->proprietaryTools->pluck('name')->filter()->values();
            } elseif (method_exists($alt, 'proprietaryTools')) {
                $names = $alt->proprietaryTools()->pluck('name')->filter()->values();
            }
        } catch (\Throwable) {
        }

        if ($names->isEmpty()) {
            $single = $alt->proprietaryTool?->name;
            if (filled($single)) {
                $names = collect([(string) $single]);
            }
        }

        return $this->joinNames($names) ?: 'proprietary tools';
    }

    /**
     * @param  \Illuminate\Support\Collection|array  $names
     */
    public function joinNames($names): string
    {
        $list = collect($names)->map(fn ($n) => trim((string) $n))->filter()->values();
        if ($list->isEmpty()) {
            return '';
        }
        if ($list->count() === 1) {
            return (string) $list[0];
        }
        if ($list->count() === 2) {
            return $list[0].' and '.$list[1];
        }

        return $list->slice(0, -1)->implode(', ').' and '.$list->last();
    }

    public function alternativeTitle(OpenSourceAlternative $alt): string
    {
        if (filled($alt->meta_title)) {
            return (string) $alt->meta_title;
        }

        $tpl = SiteSetting::get(
            'seo_title_alternative',
            '%title% Open-Source %prop% Alternative %sep% %sitename%'
        );

        return $this->replace((string) $tpl, [
            '%title%' => $alt->name,
            '%prop%' => $this->proprietaryNames($alt),
            '%license%' => $alt->license_type ?? '',
            '%language%' => $alt->primary_language ?? '',
            '%health%' => (string) round((float) $alt->overall_health_score),
        ]);
    }

    public function alternativeDescription(OpenSourceAlternative $alt): string
    {
        if (filled($alt->meta_description)) {
            return Str::limit((string) $alt->meta_description, 160);
        }

        $tpl = SiteSetting::get('seo_desc_alternative', '');
        $excerpt = $this->plainText((string) $alt->description);
        $prop = $this->proprietaryNames($alt);
        if ($prop === 'proprietary tools') {
            $prop = 'proprietary software';
        }

        if ($tpl) {
            return Str::limit($this->replace((string) $tpl, [
                '%title%' => $alt->name,
                '%prop%' => $prop,
                '%license%' => $alt->license_type ?? 'open-source',
                '%language%' => $alt->primary_language ?? '',
                '%health%' => (string) round((float) $alt->overall_health_score),
                '%excerpt%' => Str::limit($excerpt, 120),
            ]), 160);
        }

        if ($excerpt !== '') {
            return Str::limit($excerpt, 155);
        }

        return Str::limit(
            $alt->name.' is a free, self-hostable open-source alternative to '.$prop.'.',
            155
        );
    }

    /**
     * @return array{title: string, description: string, image: string|null, type: string, url: string}
     */
    public function alternativeSocial(OpenSourceAlternative $alt): array
    {
        $seoTitle = $this->alternativeTitle($alt);
        $seoDesc = $this->alternativeDescription($alt);

        $title = filled($alt->og_title) ? (string) $alt->og_title : $seoTitle;
        $description = filled($alt->og_description)
            ? Str::limit((string) $alt->og_description, 200)
            : $seoDesc;

        $image = null;
        try {
            $image = app(OgImageService::class)->alternativeUrl($alt);
        } catch (\Throwable) {
            $image = $this->defaultOgImage();
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'type' => 'article',
            'url' => $alt->canonical_url ?: route('alternatives.show', $alt),
        ];
    }

    public function toolTitle(ProprietaryTool $tool, ?int $count = null): string
    {
        if (filled($tool->meta_title)) {
            return (string) $tool->meta_title;
        }

        $tpl = SiteSetting::get(
            'seo_title_tool',
            '%count% Open Source Alternatives to %title% %sep% %sitename%'
        );

        return $this->replace((string) $tpl, [
            '%title%' => $tool->name,
            '%count%' => $count !== null ? (string) $count : '',
        ]);
    }

    public function toolDescription(ProprietaryTool $tool): string
    {
        if (filled($tool->meta_description)) {
            return Str::limit((string) $tool->meta_description, 160);
        }

        $tpl = SiteSetting::get('seo_desc_tool', '');
        $excerpt = $this->plainText((string) $tool->description);

        if ($tpl) {
            return Str::limit($this->replace((string) $tpl, [
                '%title%' => $tool->name,
                '%excerpt%' => Str::limit($excerpt, 120),
            ]), 160);
        }

        if ($excerpt !== '') {
            return Str::limit($excerpt, 155);
        }

        return Str::limit(
            'Browse free, self-hostable open-source alternatives to '.$tool->name.'.',
            155
        );
    }

    /**
     * @return array{title: string, description: string, image: string|null, type: string, url: string}
     */
    public function toolSocial(ProprietaryTool $tool, ?int $count = null, ?string $fallbackHeading = null): array
    {
        $seoTitle = $this->toolTitle($tool, $count);
        $seoDesc = $this->toolDescription($tool);

        $title = filled($tool->og_title)
            ? (string) $tool->og_title
            : ($fallbackHeading ?: $seoTitle);

        $description = filled($tool->og_description)
            ? Str::limit((string) $tool->og_description, 200)
            : $seoDesc;

        $image = null;
        try {
            $image = app(OgImageService::class)->toolUrl($tool);
        } catch (\Throwable) {
            $image = $this->defaultOgImage();
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'type' => 'article',
            'url' => $tool->canonical_url ?: route('alternativesto.show', $tool->slug),
        ];
    }

    /**
     * @return array{title: string, description: string, image: string|null, type: string, url: string}
     */
    public function collectionSocial(Collection $collection): array
    {
        $title = method_exists($collection, 'seoTitle')
            ? $collection->seoTitle()
            : ($collection->name.' '.$this->separator().' '.$this->siteName());

        $description = method_exists($collection, 'seoDescription')
            ? $collection->seoDescription()
            : Str::limit($this->plainText((string) ($collection->description ?? '')) ?: $this->defaultDescription(), 160);

        $image = null;
        try {
            $image = app(OgImageService::class)->collectionUrl($collection);
        } catch (\Throwable) {
            $image = $this->defaultOgImage();
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'type' => 'website',
            'url' => route('collections.show', $collection->slug),
        ];
    }

    public function cmsPageTitle(Page $page): string
    {
        if (filled($page->meta_title)) {
            return (string) $page->meta_title;
        }

        return $this->replace('%page% %sep% %sitename%', ['%page%' => $page->title]);
    }

    public function cmsPageDescription(Page $page): string
    {
        if (filled($page->meta_description)) {
            return Str::limit((string) $page->meta_description, 160);
        }

        $excerpt = $this->plainText((string) ($page->excerpt ?? ''));
        if ($excerpt !== '') {
            return Str::limit($excerpt, 155);
        }

        $body = $this->plainText((string) ($page->body_html ?? ''));
        if ($body !== '') {
            return Str::limit($body, 155);
        }

        return $this->defaultDescription();
    }

    /**
     * @return array{title: string, description: string, image: string|null, type: string, url: string}
     */
    public function cmsPageSocial(Page $page): array
    {
        $seoTitle = $this->cmsPageTitle($page);
        $seoDesc = $this->cmsPageDescription($page);

        $title = filled($page->og_title ?? null) ? (string) $page->og_title : $seoTitle;
        $description = filled($page->og_description ?? null)
            ? Str::limit((string) $page->og_description, 200)
            : $seoDesc;

        $image = null;
        if (filled($page->og_image_url ?? null)) {
            $image = (string) $page->og_image_url;
        } else {
            $image = $this->defaultOgImage();
        }

        return [
            'title' => $title,
            'description' => $description,
            'image' => $image,
            'type' => 'article',
            'url' => $page->canonical_url ?: (method_exists($page, 'publicUrl') ? $page->publicUrl() : url('/')),
        ];
    }

    public function pageTitle(string $pageKey, string $fallback): string
    {
        $tpl = SiteSetting::get('seo_title_'.$pageKey, '');

        if ($tpl) {
            return $this->replace((string) $tpl, ['%page%' => $fallback, '%title%' => $fallback]);
        }

        return $this->replace('%page% %sep% %sitename%', ['%page%' => $fallback]);
    }

    public function robotsMeta(?string $pageRobots = null): string
    {
        if ($this->siteNoIndex()) {
            return 'noindex,nofollow';
        }

        if ($pageRobots) {
            return $pageRobots;
        }

        return SiteSetting::get('seo_robots_default', 'index,follow,max-image-preview:large,max-snippet:-1')
            ?: 'index,follow,max-image-preview:large,max-snippet:-1';
    }

    public function privateRobots(): string
    {
        return SiteSetting::get('seo_private_robots', 'noindex,nofollow') ?: 'noindex,nofollow';
    }

    public function verifications(): array
    {
        return [
            'google' => (string) SiteSetting::get('seo_verify_google', ''),
            'bing' => (string) SiteSetting::get('seo_verify_bing', ''),
            'yandex' => (string) SiteSetting::get('seo_verify_yandex', ''),
            'pinterest' => (string) SiteSetting::get('seo_verify_pinterest', ''),
        ];
    }

    public function organizationSchema(): array
    {
        $logo = trim((string) SiteSetting::get('seo_org_logo', ''));
        $sameAs = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) SiteSetting::get('seo_org_sameas', '')) ?: []
        )));

        $org = [
            '@type' => 'Organization',
            'name' => SiteSetting::get('seo_org_name', $this->siteName()) ?: $this->siteName(),
            'url' => url('/'),
        ];

        if ($logo !== '') {
            $org['logo'] = $logo;
        }

        if ($sameAs !== []) {
            $org['sameAs'] = $sameAs;
        }

        return $org;
    }

    public function head(array $page = []): array
    {
        $title = $page['title'] ?? $this->homepageTitle();
        $description = $page['description'] ?? $this->defaultDescription();
        $canonical = $page['canonical'] ?? url()->current();
        $robots = $this->robotsMeta($page['robots'] ?? null);

        $ogTitle = filled($page['ogTitle'] ?? null) ? $page['ogTitle'] : $title;
        $ogDescription = filled($page['ogDescription'] ?? null)
            ? $page['ogDescription']
            : $description;

        $ogImage = $page['ogImage'] ?? null;
        if (! filled($ogImage)) {
            $ogImage = $this->defaultOgImage();
        }

        return [
            'title' => $title,
            'description' => Str::limit($this->plainText($description) ?: $description, 160),
            'canonical' => $canonical,
            'robots' => $robots,
            'ogType' => $page['ogType'] ?? 'website',
            'ogTitle' => $ogTitle,
            'ogDescription' => Str::limit($this->plainText($ogDescription) ?: $ogDescription, 200),
            'ogImage' => $ogImage,
            'ogSiteName' => $this->siteName(),
            'twitterCard' => filled($ogImage) ? 'summary_large_image' : ($this->twitterCard() ?: 'summary'),
            'twitterHandle' => $this->twitterHandle(),
            'verifications' => $this->verifications(),
            'facebookAppId' => SiteSetting::get('seo_facebook_app_id', ''),
        ];
    }
}
