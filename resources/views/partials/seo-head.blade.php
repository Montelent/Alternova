@php
    /** @var \App\Services\SeoManager $seo */
    $seo = app(\App\Services\SeoManager::class);
    $head = $seo->head([
        'title' => $title ?? null,
        'description' => $description ?? null,
        'canonical' => $canonical ?? null,
        'robots' => $robots ?? null,
        'ogType' => $ogType ?? 'website',
        'ogImage' => $ogImage ?? null,
        'ogTitle' => $ogTitle ?? null,
        'ogDescription' => $ogDescription ?? null,
    ]);
@endphp
<title>{{ $head['title'] }}</title>
<meta name="description" content="{{ $head['description'] }}">
<meta name="robots" content="{{ $head['robots'] }}">
<meta name="googlebot" content="{{ $head['robots'] }}">
<link rel="canonical" href="{{ $head['canonical'] }}">

<meta property="og:site_name" content="{{ $head['ogSiteName'] }}">
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $head['ogType'] }}">
<meta property="og:title" content="{{ $head['ogTitle'] }}">
<meta property="og:description" content="{{ $head['ogDescription'] }}">
<meta property="og:url" content="{{ $head['canonical'] }}">
@if(!empty($head['ogImage']))
<meta property="og:image" content="{{ $head['ogImage'] }}">
<meta property="og:image:secure_url" content="{{ $head['ogImage'] }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="{{ $head['ogTitle'] }}">
@endif
@if(!empty($head['facebookAppId']))
<meta property="fb:app_id" content="{{ $head['facebookAppId'] }}">
@endif

<meta name="twitter:card" content="{{ $head['twitterCard'] ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $head['ogTitle'] }}">
<meta name="twitter:description" content="{{ $head['ogDescription'] }}">
@if(!empty($head['twitterHandle']))
<meta name="twitter:site" content="{{ $head['twitterHandle'] }}">
@endif
@if(!empty($head['ogImage']))
<meta name="twitter:image" content="{{ $head['ogImage'] }}">
<meta name="twitter:image:alt" content="{{ $head['ogTitle'] }}">
@endif

@if(!empty($head['verifications']['google']))
<meta name="google-site-verification" content="{{ $head['verifications']['google'] }}">
@endif
@if(!empty($head['verifications']['bing']))
<meta name="msvalidate.01" content="{{ $head['verifications']['bing'] }}">
@endif
@if(!empty($head['verifications']['yandex']))
<meta name="yandex-verification" content="{{ $head['verifications']['yandex'] }}">
@endif
@if(!empty($head['verifications']['pinterest']))
<meta name="p:domain_verify" content="{{ $head['verifications']['pinterest'] }}">
@endif

@if(!empty($jsonLd))
    @foreach((array) $jsonLd as $block)
        <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endif
