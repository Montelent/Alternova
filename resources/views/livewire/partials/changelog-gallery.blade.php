@php
    $gallery = method_exists($alternative, 'galleryImageUrls')
        ? $alternative->galleryImageUrls()
        : (is_array($alternative->gallery_urls ?? null) ? $alternative->gallery_urls : []);
    $changelog = trim((string) ($alternative->changelog ?? ''));
@endphp

@if(count($gallery))
<section class="mb-8" aria-label="Screenshots">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Screenshots</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($gallery as $url)
            @if(is_string($url) && $url !== '')
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                    class="block overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 shadow-sm">
                    <img src="{{ $url }}" alt="Screenshot of {{ $alternative->name }}"
                        class="w-full h-48 sm:h-56 object-cover hover:opacity-95 transition" loading="lazy">
                </a>
            @endif
        @endforeach
    </div>
</section>
@endif

@if($changelog !== '')
<section class="mb-8 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 px-6 py-4">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Changelog &amp; notes</h2>
    </div>
    <div class="p-6 text-sm text-slate-600 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none">
        {!! $changelog !!}
    </div>
</section>
@endif
