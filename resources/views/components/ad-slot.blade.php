@props([
    'slot' => 'in_article',
    'class' => '',
])

@php
    use App\Support\AdSettings;

    $placement = $slot;
    $enabled = AdSettings::enabled();
    $showPlaceholder = AdSettings::showPlaceholders();
    $custom = AdSettings::code($placement);
    $client = AdSettings::client();
    $slotId = AdSettings::slot($placement);
    $canRender = AdSettings::canRender($placement);
    $label = AdSettings::placementLabels()[$placement] ?? str_replace('_', ' ', $placement);
@endphp

@if($canRender && $custom !== '')
    {{-- Any network: paste full unit HTML/JS in Admin → Ad settings --}}
    <div {{ $attributes->merge(['class' => 'ad-slot ad-slot-'.$placement.' '.$class, 'data-ad-slot' => $placement]) }}>
        {!! $custom !!}
    </div>
@elseif($canRender && $client !== '' && $slotId !== '')
    <div {{ $attributes->merge(['class' => 'ad-slot ad-slot-'.$placement.' '.$class, 'data-ad-slot' => $placement]) }}>
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
    <div {{ $attributes->merge(['class' => 'ad-slot-placeholder border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs text-center rounded-xl py-6 px-4 '.$class]) }}
        data-ad-slot="{{ $placement }}"
        role="presentation">
        Ad: {{ $label }} (preview — paste code or AdSense slot in Admin → Ad settings)
    </div>
@endif
