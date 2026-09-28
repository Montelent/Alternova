@props(['url' => null, 'title' => ''])

@php
    $shareUrl = urlencode($url ?? url()->current());
    $shareTitle = urlencode($title ?: config('app.name'));
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    <span class="text-xs font-semibold uppercase tracking-wide text-slate-400 mr-1">Share</span>
    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
        X / Twitter
    </a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
        LinkedIn
    </a>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}"
        target="_blank" rel="noopener noreferrer"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
        Facebook
    </a>
    <button type="button"
        onclick="navigator.clipboard.writeText(decodeURIComponent('{{ $shareUrl }}')); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy link', 1500)"
        class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
        Copy link
    </button>
</div>
