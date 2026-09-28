@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name', 'Alternova'),
        'url' => url('/'),
        'description' => 'Discover self-hostable open-source alternatives to proprietary tools. Generate brandable domain names with live availability checks.',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => url('/alternatives').'?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
