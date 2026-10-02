@props(['alternative'])
@if($alternative->logo_url)
    <img src="{{ $alternative->logo_url }}" alt="{{ $alternative->name }}"
        class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl object-contain bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm shrink-0"
        width="64" height="64" loading="lazy">
@else
    <span class="flex h-14 w-14 sm:h-16 sm:w-16 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white text-xl font-bold shadow-sm">
        {{ strtoupper(substr($alternative->name, 0, 1)) }}
    </span>
@endif
