@php
    $seo = app(\App\Services\SeoManager::class);
    $siteName = $seo->siteName();
    $title = $seo->homepageTitle();
    $description = $seo->homepageDescription();
@endphp
@component('layouts.app', ['title' => $title, 'description' => $description, 'canonical' => $seo->currentCanonicalUrl(), 'jsonLd' => [['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => $siteName, 'url' => $seo->currentCanonicalUrl()]]])
<div class="min-h-screen">
    @include('partials.home-main-top')
    @include('partials.home-main-bottom')
</div>
@endcomponent
