<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
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

    public function alternativeTitle(OpenSourceAlternative $alt): string
    {
        if ($alt->meta_title) {
            return $alt->meta_title;
        }

        $tpl = SiteSetting::get(
            'seo_title_alternative',
            '%title% Open-Source %prop% Alternative %sep% %sitename%'
        );

        return $this->replace((string) $tpl, [
            '%title%' => $alt->name,
            '%prop%' => $alt->proprietaryTool?->name ?? 'proprietary tools',
            '%license%' => $alt->license_type ?? '',
            '%language%' => $alt->primary_language ?? '',
            '%health%' => (string) round((float) $alt->overall_health_score),
        ]);
    }

    public function alternativeDescription(OpenSourceAlternative $alt): string
    {
        if ($alt->meta_description) {
            return $alt->meta_description;
        }

        $tpl = SiteSetting::get('seo_desc_alternative', '');

        if ($tpl) {
            return Str::limit($this->replace((string) $tpl, [
                '%title%' => $alt->name,
                '%prop%' => $alt->proprietaryTool?->name ?? 'proprietary software',
                '%license%' => $alt->license_type ?? 'open-source',
                '%language%' => $alt->primary_language ?? '',
                '%health%' => (string) round((float) $alt->overall_health_score),
                '%excerpt%' => Str::limit(strip_tags((string) $alt->description), 120),
            ]), 160);
        }

        return Str::limit(
            $alt->name.' is a free, self-hostable open-source alternative to '
            .($alt->proprietaryTool?->name ?? 'proprietary software').'. '
            .strip_tags((string) ($alt->description ?? '')),
            155
        );
    }

    public function toolTitle(ProprietaryTool $tool, ?int $count = null): string
    {
        if ($tool->meta_title) {
            return $tool->meta_title;
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
        if ($tool->meta_description) {
            return $tool->meta_description;
        }

        $tpl = SiteSetting::get('seo_desc_tool', '');

        if ($tpl) {
            return Str::limit($this->replace((string) $tpl, [
                '%title%' => $tool->name,
                '%excerpt%' => Str::limit(strip_tags((string) $tool->description), 120),
            ]), 160);
        }

        return Str::limit(
            'Browse free, self-hostable open-source alternatives to '.$tool->name.'. '
            .strip_tags((string) ($tool->description ?? '')),
            155
        );
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

    /** Private / account surfaces that should not be indexed. */
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
        $ogImage = $page['ogImage'] ?? $this->defaultOgImage();

        return [
            'title' => $title,
            'description' => Str::limit($description, 160),
            'canonical' => $canonical,
            'robots' => $robots,
            'ogType' => $page['ogType'] ?? 'website',
            'ogTitle' => $page['ogTitle'] ?? $title,
            'ogDescription' => Str::limit($page['ogDescription'] ?? $description, 160),
            'ogImage' => $ogImage,
            'ogSiteName' => $this->siteName(),
            'twitterCard' => $this->twitterCard(),
            'twitterHandle' => $this->twitterHandle(),
            'verifications' => $this->verifications(),
            'facebookAppId' => SiteSetting::get('seo_facebook_app_id', ''),
        ];
    }
}
