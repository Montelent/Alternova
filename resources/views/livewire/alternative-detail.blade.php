<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition">Home</a></li>
                <li class="text-slate-300">/</li>
                <li><a href="{{ route('finder') }}" class="hover:text-brand-600 transition">Alternatives</a></li>
                <li class="text-slate-300">/</li>
                <li class="text-slate-800 dark:text-slate-200 font-medium truncate max-w-[12rem]">{{ $alternative->name }}</li>
            </ol>
        </nav>

        <header class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm mb-8">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-violet-50 dark:from-brand-950/40 dark:via-slate-900 dark:to-slate-900 pointer-events-none"></div>
            <div class="relative p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 mb-3">Open-source alternative</p>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15]">{{ $heading }}</h1>
                        <p class="mt-3 text-lg text-slate-600 dark:text-slate-300 font-medium">{{ $subheading }}</p>
                        <div class="mt-5 flex flex-wrap gap-2 items-center">
                            @if($alternative->license_type)
                                <span class="rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 px-3 py-1 text-xs font-semibold">{{ $alternative->license_type }}</span>
                            @endif
                            @if($alternative->primary_language)
                                <span class="rounded-full bg-sky-50 text-sky-700 border border-sky-100 px-3 py-1 text-xs font-semibold">{{ $alternative->primary_language }}</span>
                            @endif
                            <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-3 py-1 text-xs font-semibold">Difficulty {{ $alternative->self_host_difficulty }}/5</span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 text-amber-800 border border-amber-100 px-3 py-1 text-xs font-semibold">
                                Health {{ number_format($alternative->overall_health_score, 1) }}/100
                                <x-health-sparkline :points="$healthPoints" :trend="$healthTrend" class="w-12 h-4" />
                            </span>
                        </div>
                        @if($alternative->description)
                            <p class="mt-6 text-slate-600 dark:text-slate-300 leading-relaxed">{{ $alternative->description }}</p>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-5 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            View on GitHub
                        </a>
                        @if($alternative->website_url)
                            <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 transition">
                                Official website
                            </a>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="vote" class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium {{ $hasVoted ? 'bg-brand-50 text-brand-700' : '' }}">
                                ▲ {{ $votesCount }}
                            </button>
                            <button type="button" wire:click="toggleFavorite" class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium {{ $isFavorited ? 'text-amber-500' : '' }}">
                                {{ $isFavorited ? '★ Saved' : '☆ Save' }}
                            </button>
                            <button type="button" wire:click="toggleCompare" class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-medium">
                                {{ $inCompare ? 'In compare' : 'Compare' }}
                            </button>
                        </div>
                        @if($voteMessage)<p class="text-xs text-slate-500">{{ $voteMessage }}</p>@endif
                        @if($favoriteMessage)<p class="text-xs text-slate-500">{{ $favoriteMessage }}</p>@endif
                        @if($compareMessage)<p class="text-xs text-slate-500">{{ $compareMessage }} @if($compareUrl)<a href="{{ $compareUrl }}" class="text-brand-600">Open</a>@endif</p>@endif
                    </div>
                </div>
            </div>
        </header>

        @if($metric)
            <section class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold">{{ number_format($metric->github_stars) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">Stars</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold">{{ number_format($metric->github_forks) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">Forks</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold">{{ number_format($metric->open_issues) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500">Open issues</div>
                </div>
                <div class="rounded-2xl border border-brand-100 bg-brand-50/50 dark:bg-brand-950/30 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-brand-700">{{ number_format($alternative->overall_health_score, 1) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-brand-600">Health</div>
                </div>
            </section>
        @endif

        <x-health-chart :series="$healthSeries" :points="$healthPoints" :trend="$healthTrend" />

        @if($alternative->docker_compose_blueprint)
            <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-bold">Docker Compose blueprint</h2>
                    <button type="button" @click="navigator.clipboard.writeText($refs.code.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                        class="rounded-lg border border-slate-200 dark:border-slate-700 px-3 py-1.5 text-xs font-semibold">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-emerald-600">Copied</span>
                    </button>
                </div>
                <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto text-xs sm:text-sm"><code x-ref="code">{{ $alternative->docker_compose_blueprint }}</code></pre>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-bold mb-3">Why {{ $alternative->name }}?</h2>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm sm:text-base">
                <strong>{{ $alternative->name }}</strong> is a self-hostable open-source alternative to <strong>{{ $propName }}</strong>,
                with health score {{ number_format($alternative->overall_health_score, 1) }}/100
                @if($alternative->license_type) under the {{ $alternative->license_type }} license @endif.
            </p>
        </section>

        @if($related->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-xl font-bold mb-4">Related alternatives</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('alternatives.show', $rel) }}" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:border-brand-300 transition">
                            <p class="font-semibold">{{ $rel->name }}</p>
                            <p class="text-xs text-slate-500 mt-1">Health {{ number_format($rel->overall_health_score, 1) }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mb-10">
            <livewire:comment-section :alternative-id="$alternative->id" :key="'comments-'.$alternative->id" />
        </div>
    </div>
</div>
