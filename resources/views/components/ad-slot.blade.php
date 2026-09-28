@props([
    'slot' => 'in_article', // header|in_article|sidebar|footer
    'class' => '',
])

@php
    $enabled = config('ads.enabled');
    $showPlaceholder = config('ads.show_placeholders');
    $client = config('ads.adsense.client');
    $slotId = config('ads.adsense.slots.'.$slot, '');
    $canRender = $enabled && $client && $slotId;
@endphp

@if($canRender)
    <div {{ $attributes->merge(['class' => 'ad-slot ad-slot-'.$slot.' '.$class, 'data-ad-slot' => $slot]) }}>
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="{{ $client }}"
            data-ad-slot="{{ $slotId }}"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
@elseif($showPlaceholder)
    <div {{ $attributes->merge(['class' => 'ad-slot-placeholder border border-dashed border-slate-300 bg-slate-50 text-slate-400 text-xs text-center rounded-xl py-6 px-4 '.$class]) }}
        data-ad-slot="{{ $slot }}"
        role="presentation">
        Ad placement: {{ str_replace('_', ' ', $slot) }} (not live — ADS_ENABLED=false)
    </div>
@endif
