<div class="flex flex-wrap items-center gap-3 mb-6">
    <button type="button"
        wire:click="toggleFavorite"
        class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-sm font-semibold transition
            {{ $isFavorited
                ? 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200'
                : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
        <span>{{ $isFavorited ? '★' : '☆' }}</span>
        {{ $isFavorited ? 'Favorited' : 'Save favorite' }}
    </button>

    @if(!empty($badgeUrl))
        <a href="{{ $badgeUrl }}" target="_blank" rel="noopener" class="inline-flex items-center" title="Embeddable health badge">
            <img src="{{ $badgeUrl }}" alt="Health badge" height="20" class="h-5">
        </a>
        <code class="hidden sm:inline text-[10px] text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded max-w-[14rem] truncate" title="Copy for README">{{ $badgeUrl }}</code>
    @endif

    @if(!empty($favoriteMessage))
        <span class="text-xs text-slate-500">{{ $favoriteMessage }}</span>
    @endif
</div>
