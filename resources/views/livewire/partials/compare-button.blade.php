<div class="inline-flex flex-wrap items-center gap-2">
    <button type="button"
        wire:click="toggleCompare"
        class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-sm font-semibold transition
            {{ $inCompare
                ? 'border-violet-300 bg-violet-50 text-violet-800 dark:border-violet-700 dark:bg-violet-950/40 dark:text-violet-200'
                : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        {{ $inCompare ? 'In compare' : 'Add to compare' }}
    </button>

    @if(!empty($compareUrl))
        <a href="{{ $compareUrl }}"
            class="text-sm font-semibold text-brand-600 hover:underline">
            Compare now →
        </a>
    @elseif(!empty($compareMessage))
        <span class="text-xs text-slate-500">{{ $compareMessage }}</span>
    @endif
</div>
