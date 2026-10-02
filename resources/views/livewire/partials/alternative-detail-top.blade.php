<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    @foreach(($schemas ?? []) as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Home</a></li>
                <li class="text-slate-300">/</li>
                <li><a href="{{ route('finder') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Alternatives</a></li>
                <li class="text-slate-300">/</li>
                <li class="text-slate-800 dark:text-slate-200 font-medium truncate max-w-[12rem]">{{ $alternative->name }}</li>
            </ol>
        </nav>

        <header class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm mb-8">
            <div class="relative p-6 sm:p-10">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-3">Open-source alternative</p>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $heading ?? $alternative->name }}</h1>

                <p class="mt-3 text-lg text-slate-600 dark:text-slate-300 font-medium">
                    {{ $subheading ?? 'Open-source alternative' }}
                </p>

                <div class="mt-5 flex flex-wrap gap-2 items-center">
                    @if($alternative->license_type)
                        <span class="rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 px-3 py-1 text-xs font-semibold">{{ $alternative->license_type }}</span>
                    @endif
                    @if($alternative->primary_language)
                        <span class="rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-200 px-3 py-1 text-xs font-semibold">{{ $alternative->primary_language }}</span>
                    @endif
                    <span class="rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200 px-3 py-1 text-xs font-semibold">Difficulty {{ $alternative->self_host_difficulty }}/5</span>
                    <span class="rounded-full bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200 px-3 py-1 text-xs font-semibold">
                        Health {{ number_format($alternative->overall_health_score, 1) }}/100
                    </span>
                </div>

                @if($alternative->description)
                    <div class="mt-6 text-slate-600 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none">
                        {!! $alternative->description !!}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if($alternative->website_url)
                        <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-4 py-2.5">Website</a>
                    @endif
                    @if($alternative->repo_url)
                        <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-semibold px-4 py-2.5">Repository</a>
                    @endif
                    <button type="button" wire:click="vote" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2 text-sm font-medium">▲ {{ $votesCount }}</button>
                    <button type="button" wire:click="toggleFavorite" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2 text-sm font-medium">{{ $isFavorited ? '★ Saved' : '☆ Save' }}</button>
                    <button type="button" wire:click="toggleWatch" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2 text-sm font-medium">{{ $isWatching ? 'Watching' : 'Watch' }}</button>
                    <button type="button" wire:click="toggleCompare" class="rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-2 text-sm font-medium">{{ $inCompare ? 'In compare' : 'Compare' }}</button>
                </div>
            </div>
        </header>

        <section class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_stars ?? 0) }}</div>
                <div class="text-xs text-slate-500">GitHub stars</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_forks ?? 0) }}</div>
                <div class="text-xs text-slate-500">Forks</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->open_issues ?? 0) }}</div>
                <div class="text-xs text-slate-500">Open issues</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($alternative->overall_health_score, 1) }}</div>
                <div class="text-xs text-slate-500">Health</div>
            </div>
        </section>

        @if(!empty($healthPoints))
            <x-health-chart :series="$healthSeries" :points="$healthPoints" :trend="$healthTrend" />
        @endif
