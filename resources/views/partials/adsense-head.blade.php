@if(config('ads.enabled') && config('ads.adsense.client'))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('ads.adsense.client') }}"
        crossorigin="anonymous"></script>
@endif
