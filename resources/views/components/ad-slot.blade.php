@props([
    'slot' => 'in_article',
    'class' => '',
])

@php
    use App\Support\AdSettings;

    $enabled = AdSettings::enabled();
    $showPlaceholder = AdSettings::showPlaceholders();
    $client = AdSettings::client();
    $slotId = AdSettings::slot($slot);
    $canRender = AdSettings::canRender($slot);
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
        Ad placement: {{ str_replace('_', ' ', $slot) }} (preview only — enable live ads in Admin → Ad settings)
    </div>
@endif
