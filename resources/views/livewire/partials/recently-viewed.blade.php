@if(isset($recent) && $recent->isNotEmpty())
<section class="mt-10 mb-8" aria-label="Recently viewed">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Recently viewed</h2>
    <div class="flex gap-3 overflow-x-auto pb-2">
        @foreach($recent as $item)
            <a href="{{ route('alternatives.show', $item) }}"
                class="shrink-0 w-48 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 hover:border-brand-300 transition">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $item->name }}</p>
                <p class="text-xs text-slate-500 mt-1 truncate">{{ $item->proprietaryTool?->name }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif
