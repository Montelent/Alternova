@props(['alt', 'size' => 'md'])
@php
    $box = $size === 'lg' ? 'h-14 w-14 sm:h-16 sm:w-16 rounded-2xl text-xl' : 'h-10 w-10 rounded-xl text-sm';
@endphp
@if($alt->logo_url)
    <img src="{{ $alt->logo_url }}" alt="{{ $alt->name }}"
        class="{{ $box }} object-contain bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0"
        loading="lazy">
@else
    <span class="flex {{ $box }} shrink-0 items-center justify-center bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-200 font-bold border border-brand-100 dark:border-brand-900">
        {{ strtoupper(substr($alt->name, 0, 1)) }}
    </span>
@endif
