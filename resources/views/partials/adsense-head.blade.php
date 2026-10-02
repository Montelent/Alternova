@php
    use App\Support\AdSettings;
@endphp
{{-- Global ad head scripts (any network) --}}
@if(AdSettings::enabled() && AdSettings::headCode() !== '')
    {!! AdSettings::headCode() !!}
@endif
{{-- Auto-load AdSense library when using slot IDs without custom HTML --}}
@if(AdSettings::shouldLoadAdsenseScript())
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ AdSettings::client() }}"
        crossorigin="anonymous"></script>
@endif
