<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-2">Curated</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">Open-source collections</h1>
            <p class="mt-3 text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
                Editor-picked lists of self-hostable alternatives — by category, use case, or comparison theme.
            </p>
        </div>

        @if($collections->isEmpty())
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-12 text-center">
                <p class="text-slate-500">No published collections yet.</p>
                <a href="{{ route('finder') }}" class="mt-4 inline-block text-sm font-semibold text-brand-600 dark:text-brand-300 hover:underline">Browse alternatives →</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 gap-5">
                @foreach($collections as $c)
                    <a href="{{ route('collections.show', $c->slug) }}"
                        class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 hover:border-brand-400 dark:hover:border-brand-500 shadow-sm transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                @if($c->is_featured)
                                    <span class="inline-block rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200 text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 mb-2">Featured</span>
                                @endif
                                <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300 transition">{{ $c->name }}</h2>
                            </div>
                            <span class="shrink-0 text-xs font-medium text-slate-400">{{ $c->items_count }} tools</span>
                        </div>
                        @if($c->description)
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 line-clamp-3">{{ $c->description }}</p>
                        @endif
                        <span class="mt-4 inline-flex text-sm font-medium text-brand-600 dark:text-brand-300">View collection →</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
