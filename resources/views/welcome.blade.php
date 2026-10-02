@php
    $seo = app(\App\Services\SeoManager::class);
    $siteName = $seo->siteName();
    $title = $seo->homepageTitle();
    $description = $seo->homepageDescription();
@endphp
@component('layouts.app', ['title' => $title, 'description' => $description, 'canonical' => url('/')])
<div class="min-h-screen">
    @include('partials.home-main-top')
    @include('partials.home-main-bottom')
</div>
@endcomponent
