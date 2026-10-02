@php
    $title = $previewTitle ?? 'SEO title preview';
    $desc = $previewDesc ?? 'Meta description will appear here.';
    $slug = $previewSlug ?? 'your-slug';
    $origin = $siteOrigin ?? '';
    if ($origin === '' && ! empty($_SERVER['HTTP_HOST'])) {
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $origin = $scheme.'://'.$_SERVER['HTTP_HOST'];
    }
    if ($origin === '') {
        $origin = rtrim((string) config('app.url'), '/');
    }
@endphp

<div
    wire:key="serp-{{ md5(($title ?? '').($desc ?? '').($slug ?? '')) }}"
    class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-950 p-4 space-y-1 shadow-sm"
>
    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Google-style preview (updates as you type)</p>
    <p class="text-xl text-[#1a0dab] dark:text-sky-400 leading-snug line-clamp-2">{{ \Illuminate\Support\Str::limit($title, 70) }}</p>
    <p class="text-sm text-[#006621] dark:text-emerald-400 truncate">
        {{ $origin }}/{{ ltrim($slug, '/') }}
    </p>
    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ \Illuminate\Support\Str::limit($desc, 160) }}</p>
</div>
