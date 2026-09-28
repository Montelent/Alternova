@php
    use App\Support\AdSettings;
    $client = AdSettings::client();
@endphp
@if(AdSettings::enabled() && $client !== '')
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $client }}"
        crossorigin="anonymous"></script>
@endif
