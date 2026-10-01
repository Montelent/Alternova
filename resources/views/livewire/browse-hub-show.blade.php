<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('browse.type', $type) }}" class="hover:text-brand-600 capitalize">{{ $type }}</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $meta['name'] }}</span>
        </nav>

        <header class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $heading }}</h1>
            <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">
                {{ $alternatives->total() }} published project{{ $alternatives->total() === 1 ? '' : 's' }}.
                <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-400 font-semibold hover:underline">Open full finder</a>
            </p>
        </header>

        @if($alternatives->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-600 dark:text-slate-400">
                No published alternatives in this group yet.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                @foreach($alternatives as $alt)
                    <a href="{{ route('alternatives.show', $alt) }}"
                        class="group flex flex-col rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition h-full">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 leading-snug">
                                {{ $alt->name }}
                            </h2>
                            <span class="shrink-0 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 text-xs font-bold px-2.5 py-1">
                                {{ number_format($alt->overall_health_score, 0) }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            @if($alt->proprietaryTool)
                                vs {{ $alt->proprietaryTool->name }}
                            @else
                                Open source
                            @endif
                        </p>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 line-clamp-3 flex-1">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $alt->description), 140) }}
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-200 text-[11px] font-semibold px-2.5 py-0.5">{{ $alt->license_type }}</span>
                            @endif
                            @if($alt->primary_language)
                                <span class="rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-800 dark:text-sky-200 text-[11px] font-semibold px-2.5 py-0.5">{{ $alt->primary_language }}</span>
                            @endif
                            @if($alt->repoMetric && $alt->repoMetric->github_stars)
                                <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-semibold px-2.5 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $alternatives->links() }}
            </div>
        @endif
    </div>
</div>
