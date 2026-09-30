<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Home</a>
            <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
            <a href="{{ route('collections.index') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Collections</a>
            <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $collection->name }}</span>
        </nav>

        <header class="mb-10">
            @if($collection->is_featured)
                <span class="inline-block rounded-full bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200 text-[10px] font-bold uppercase tracking-wide px-2.5 py-0.5 mb-3">Featured collection</span>
            @endif
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $collection->name }}</h1>
            @if($collection->description)
                <p class="mt-3 text-lg text-slate-600 dark:text-slate-300">{{ $collection->description }}</p>
            @endif
            @if($collection->intro_html)
                <div class="mt-6 prose prose-slate dark:prose-invert max-w-none text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                    {!! nl2br(e($collection->intro_html)) !!}
                </div>
            @endif
            <p class="mt-4 text-sm text-slate-500">{{ $items->count() }} open-source alternative{{ $items->count() === 1 ? '' : 's' }}</p>
        </header>

        <ol class="space-y-4">
            @forelse($items as $i => $item)
                @php $alt = $item->alternative; @endphp
                <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/15 text-brand-700 dark:text-brand-200 text-sm font-bold">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('alternatives.show', $alt) }}" class="text-lg font-semibold text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-300">{{ $alt->name }}</a>
                                    @if($alt->proprietaryTool)
                                        <p class="text-sm text-slate-500">vs {{ $alt->proprietaryTool->name }}</p>
                                    @endif
                                </div>
                                <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">{{ number_format($alt->overall_health_score, 1) }} health</span>
                            </div>
                            @if($alt->description)
                                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ $alt->description }}</p>
                            @endif
                            @if($item->note)
                                <p class="mt-2 text-sm text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-500/10 rounded-lg px-3 py-2">{{ $item->note }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-500">
                                @if($alt->license_type)
                                    <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">{{ $alt->license_type }}</span>
                                @endif
                                @if($alt->repoMetric)
                                    <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                                @endif
                                <a href="{{ route('alternatives.show', $alt) }}" class="font-medium text-brand-600 dark:text-brand-300 hover:underline">Details →</a>
                            </div>
                        </div>
                    </div>
                </li>
            @empty
                <li class="text-center py-12 text-slate-500">This collection has no published alternatives yet.</li>
            @endforelse
        </ol>

        <p class="mt-10 text-center text-sm text-slate-500">
            <a href="{{ route('collections.index') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">← All collections</a>
            ·
            <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">Browse catalog</a>
        </p>
    </div>
</div>
