<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    @foreach($schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav aria-label="Breadcrumb" class="mb-6">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600 dark:hover:text-brand-300 transition">Home</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li><a href="{{ route('finder') }}" class="hover:text-brand-600 dark:hover:text-brand-300 transition">Alternatives</a></li>
                <li class="text-slate-300 dark:text-slate-600">/</li>
                <li class="text-slate-800 dark:text-slate-200 font-medium truncate max-w-[12rem]">{{ $alternative->name }}</li>
            </ol>
        </nav>

        <header class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm mb-8">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-violet-50 dark:from-brand-950/50 dark:via-slate-900 dark:to-slate-900 pointer-events-none"></div>
            <div class="relative p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-3">Open-source alternative</p>
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15]">{{ $heading }}</h1>

                        <p class="mt-3 text-lg text-slate-600 dark:text-slate-300 font-medium">
                            The open-source alternative to
                            @php
                                $propTools = collect();
                                if (isset($proprietaryTools) && $proprietaryTools->isNotEmpty()) {
                                    $propTools = $proprietaryTools;
                                } elseif ($proprietary) {
                                    $propTools = collect([$proprietary]);
                                }
                            @endphp
                            @forelse($propTools as $pt)
                                <a href="{{ route('alternativesto.show', $pt->slug) }}"
                                    class="inline-flex items-center gap-1.5 font-semibold text-brand-600 dark:text-brand-400 hover:underline underline-offset-2">
                                    @if($pt->logo_url)
                                        <img src="{{ $pt->logo_url }}" alt="" class="h-5 w-5 rounded object-contain" loading="lazy">
                                    @endif
                                    {{ $pt->name }}
                                </a>@if(!$loop->last)<span class="text-slate-400">,</span> @endif
                            @empty
                                <span class="font-semibold text-slate-800 dark:text-slate-200">proprietary tools</span>
                            @endforelse
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2 items-center">
                            @if($alternative->license_type)
                                <span class="rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 px-3 py-1 text-xs font-semibold">{{ $alternative->license_type }}</span>
                            @endif
                            @if($alternative->primary_language)
                                <span class="rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-200 border border-sky-200 dark:border-sky-800 px-3 py-1 text-xs font-semibold">{{ $alternative->primary_language }}</span>
                            @endif
                            <span class="rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-3 py-1 text-xs font-semibold">Difficulty {{ $alternative->self_host_difficulty }}/5</span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200 border border-amber-200 dark:border-amber-800 px-3 py-1 text-xs font-semibold">
                                Health {{ number_format($alternative->overall_health_score, 1) }}/100
                                <x-health-sparkline :points="$healthPoints" :trend="$healthTrend" class="w-12 h-4" />
                            </span>
                        </div>
                        @if($alternative->description)
                            <div class="mt-6 text-slate-600 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none">
                                {!! $alternative->description !!}
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                        <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-5 py-3 text-sm font-semibold text-slate-800 dark:text-white shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                            View on GitHub
                        </a>
                        @if($alternative->website_url)
                            <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 transition">
                                Official website
                            </a>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="vote" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 {{ $hasVoted ? 'ring-1 ring-brand-400 text-brand-700 dark:text-brand-200' : '' }}">
                                ▲ {{ $votesCount }}
                            </button>
                            <button type="button" wire:click="toggleFavorite" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 {{ $isFavorited ? 'text-amber-500' : '' }}">
                                {{ $isFavorited ? '★ Saved' : '☆ Save' }}
                            </button>
                            <button type="button" wire:click="toggleWatch" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 {{ $isWatching ? 'ring-1 ring-sky-400 text-sky-700 dark:text-sky-300' : '' }}">
                                {{ $isWatching ? '👁 Watching' : '👁 Watch' }}
                            </button>
                            <button type="button" wire:click="toggleCompare" class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ $inCompare ? 'In compare' : 'Compare' }}
                            </button>
                        </div>
                        @if($voteMessage)<p class="text-xs text-slate-500 dark:text-slate-400">{{ $voteMessage }}</p>@endif
                        @if($favoriteMessage)<p class="text-xs text-slate-500 dark:text-slate-400">{{ $favoriteMessage }}</p>@endif
                        @if($watchMessage)<p class="text-xs text-slate-500 dark:text-slate-400">{{ $watchMessage }}</p>@endif
                        @if($compareMessage)<p class="text-xs text-slate-500 dark:text-slate-400">{{ $compareMessage }} @if($compareUrl)<a href="{{ $compareUrl }}" class="text-brand-600 dark:text-brand-300">Open</a>@endif</p>@endif
                    </div>
                </div>
            </div>
        </header>

        @if($metric)
            <section class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_stars) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Stars</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_forks) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Forks</div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->open_issues) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Open issues</div>
                </div>
                <div class="rounded-2xl border border-brand-200 dark:border-brand-700 bg-brand-50 dark:bg-brand-950/60 p-5 text-center shadow-sm">
                    <div class="text-2xl font-bold text-brand-700 dark:text-brand-300">{{ number_format($alternative->overall_health_score, 1) }}</div>
                    <div class="mt-1 text-xs uppercase tracking-wide text-brand-600 dark:text-brand-400">Health</div>
                </div>
            </section>
        @endif

        <x-health-chart :series="$healthSeries" :points="$healthPoints" :trend="$healthTrend" />

        @include('livewire.partials.changelog-gallery', ['alternative' => $alternative])

        @php
            $curatedPros = array_values(array_filter((array) ($alternative->pros ?? [])));
            $curatedCons = array_values(array_filter((array) ($alternative->cons ?? [])));
        @endphp
        @if(count($curatedPros) || count($curatedCons))
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" aria-labelledby="curated-pros-cons-heading">
                <div class="mb-6">
                    <h2 id="curated-pros-cons-heading" class="text-xl font-bold text-slate-900 dark:text-white">Pros & cons</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Editor summary for {{ $alternative->name }}.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 mb-3 flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-xs">+</span>
                            Pros
                        </h3>
                        @if(count($curatedPros))
                            <ul class="space-y-2">
                                @foreach($curatedPros as $pro)
                                    <li class="flex gap-2.5 items-start rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/60 dark:bg-emerald-950/25 px-3 py-2.5">
                                        <span class="mt-0.5 shrink-0 text-emerald-600 dark:text-emerald-400 font-bold" aria-hidden="true">✓</span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100 leading-snug">{{ $pro }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 dark:text-slate-500">No pros listed yet.</p>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wide text-rose-600 dark:text-rose-400 mb-3 flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 text-xs">−</span>
                            Cons
                        </h3>
                        @if(count($curatedCons))
                            <ul class="space-y-2">
                                @foreach($curatedCons as $con)
                                    <li class="flex gap-2.5 items-start rounded-xl border border-rose-100 dark:border-rose-900/40 bg-rose-50/60 dark:bg-rose-950/25 px-3 py-2.5">
                                        <span class="mt-0.5 shrink-0 text-rose-600 dark:text-rose-400 font-bold" aria-hidden="true">−</span>
                                        <span class="text-sm text-slate-800 dark:text-slate-100 leading-snug">{{ $con }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-slate-400 dark:text-slate-500">No cons listed yet.</p>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <div class="mb-8">
            <livewire:pros-cons-section :alternative-id="$alternative->id" :key="'proscons-'.$alternative->id" />
        </div>

        @if($alternative->docker_compose_blueprint)
            <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8" x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Docker Compose blueprint</h2>
                    <button type="button" @click="navigator.clipboard.writeText($refs.code.textContent); copied = true; setTimeout(() => copied = false, 2000)"
                        class="rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        <span x-show="!copied">Copy</span>
                        <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">Copied</span>
                    </button>
                </div>
                <pre class="bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto text-xs sm:text-sm"><code x-ref="code">{{ $alternative->docker_compose_blueprint }}</code></pre>
            </section>
        @endif

        <section class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8 mb-8">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Why {{ $alternative->name }}?</h2>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed text-sm sm:text-base">
                <strong class="text-slate-900 dark:text-white">{{ $alternative->name }}</strong> is a self-hostable open-source alternative to
                @forelse($propTools as $pt)
                    <a href="{{ route('alternativesto.show', $pt->slug) }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">{{ $pt->name }}</a>@if(!$loop->last), @endif
                @empty
                    <strong class="text-slate-900 dark:text-white">proprietary software</strong>
                @endforelse,
                with health score {{ number_format($alternative->overall_health_score, 1) }}/100
                @if($alternative->license_type) under the {{ $alternative->license_type }} license @endif.
            </p>
        </section>

        @if($related->isNotEmpty())
            <section class="mb-10">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Related alternatives</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($related as $rel)
                        <a href="{{ route('alternatives.show', $rel) }}" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 hover:border-brand-400 dark:hover:border-brand-500 transition">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $rel->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Health {{ number_format($rel->overall_health_score, 1) }}</p>
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
