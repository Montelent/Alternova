@props([
    'placement' => 'in_article',
    'class' => '',
])

@php
    use App\Support\AdSettings;

    // Never use $slot here — it is reserved by Blade for component content (ComponentSlot).
    $name = is_string($placement) ? $placement : 'in_article';
    $extraClass = is_string($class) ? $class : '';

    $showPlaceholder = AdSettings::showPlaceholders();
    $custom = AdSettings::code($name);
    $client = AdSettings::client();
    $slotId = AdSettings::slot($name);
    $canRender = AdSettings::canRender($name);
    $label = AdSettings::placementLabels()[$name] ?? str_replace('_', ' ', $name);
@endphp

@if($canRender && $custom !== '')
    <div {{ $attributes->merge(['class' => trim('ad-slot ad-slot-'.$name.' '.$extraClass), 'data-ad-slot' => $name]) }}>
        {!! $custom !!}
    </div>
@elseif($canRender && $client !== '' && $slotId !== '')
    <div {{ $attributes->merge(['class' => trim('ad-slot ad-slot-'.$name.' '.$extraClass), 'data-ad-slot' => $name]) }}>
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
    <div {{ $attributes->merge(['class' => trim('ad-slot-placeholder border border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/50 text-slate-500 dark:text-slate-400 text-xs text-center rounded-xl py-6 px-4 '.$extraClass), 'data-ad-slot' => $name]) }}
        role="presentation">
        Ad: {{ $label }} (preview — paste code or AdSense slot in Admin → Ad settings)
    </div>
@endif
