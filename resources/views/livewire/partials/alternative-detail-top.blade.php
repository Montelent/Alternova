@php
    $propTools = collect();
    try {
        if (isset($proprietaryTools) && $proprietaryTools->isNotEmpty()) {
            $propTools = $proprietaryTools;
        } elseif (!empty($proprietary)) {
            $propTools = collect([$proprietary]);
        }
    } catch (\Throwable) {
        if (!empty($proprietary)) {
            $propTools = collect([$proprietary]);
        }
    }
@endphp
<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    @foreach(($schemas ?? []) as $schema)
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
            <div class="absolute inset-0 bg-gradient-to-br from-brand-50 via-white to-violet-50 dark:from-brand-950/40 dark:via-slate-900 dark:to-slate-900 pointer-events-none"></div>
            <div class="relative p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-8">
                    <div class="max-w-2xl min-w-0">
                        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-3">Open-source alternative</p>
                        <div class="flex items-start gap-4">
                            @include('components.alternative-logo-lg', ['alternative' => $alternative])
                            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.15]">
                                {{ $heading ?? $alternative->name }}
                            </h1>
                        </div>

                        <p class="mt-3 text-base sm:text-lg text-slate-600 dark:text-slate-300 font-medium leading-relaxed">
                            The open-source alternative to
                            @forelse($propTools as $pt)
                                <a href="{{ route('alternativesto.show', $pt->slug) }}"
                                    class="inline-flex items-center gap-1.5 font-semibold text-brand-600 dark:text-brand-400 hover:underline underline-offset-2">
                                    @if($pt->logo_url)
                                        <img src="{{ $pt->logo_url }}" alt="" class="h-5 w-5 rounded object-contain bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700" loading="lazy" width="20" height="20">
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
                            <span class="rounded-full bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200 border border-amber-200 dark:border-amber-800 px-3 py-1 text-xs font-semibold">
                                Health {{ number_format($alternative->overall_health_score, 1) }}/100
                            </span>
                        </div>

                        @if($alternative->description)
                            <div class="mt-6 text-slate-600 dark:text-slate-300 leading-relaxed prose dark:prose-invert max-w-none text-sm sm:text-base">
                                {!! $alternative->description !!}
                            </div>
                        @endif

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if($alternative->website_url)
                                <a href="{{ $alternative->website_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-4 py-2.5 transition">Website</a>
                            @endif
                            @if($alternative->repo_url)
                                <a href="{{ $alternative->repo_url }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-800 dark:text-slate-100 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 transition">Repository</a>
                            @endif
                            <button type="button" wire:click="vote"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 {{ !empty($hasVoted) ? 'ring-1 ring-brand-400 text-brand-700 dark:text-brand-200' : '' }}">
                                ▲ {{ $votesCount ?? 0 }}
                            </button>
                            <button type="button" wire:click="toggleFavorite"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ !empty($isFavorited) ? '★ Saved' : '☆ Save' }}
                            </button>
                            <button type="button" wire:click="toggleWatch"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ !empty($isWatching) ? 'Watching' : 'Watch' }}
                            </button>
                            <button type="button" wire:click="toggleCompare"
                                class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ !empty($inCompare) ? 'In compare' : 'Compare' }}
                            </button>
                        </div>

                        {{-- Social share — engaging OG already set via OgImageService --}}
                        <div class="mt-6">
                            @php
                                $__shareProp = $propTools->pluck('name')->filter()->join(', ') ?: 'proprietary tools';
                                $__shareBits = array_filter([
                                    $alternative->license_type ?: null,
                                    $alternative->overall_health_score > 0 ? ('Health '.number_format($alternative->overall_health_score, 0).'/100') : null,
                                    isset($metric) && ($metric->github_stars ?? 0) > 0 ? ('★ '.number_format($metric->github_stars)) : null,
                                ]);
                                $__shareText = $alternative->name.' — open-source alternative to '.$__shareProp
                                    .($__shareBits ? ' · '.implode(' · ', $__shareBits) : '');
                                $__shareUrl = route('alternatives.show', $alternative);
                                try {
                                    $__shareImage = app(\App\Services\OgImageService::class)->alternativeUrl($alternative);
                                } catch (\Throwable) {
                                    $__shareImage = null;
                                }
                            @endphp
                            <x-share-buttons
                                :url="$__shareUrl"
                                :title="$alternative->name.' — open-source alternative'"
                                :text="$__shareText"
                                :image="$__shareImage"
                            />
                        </div>

                        @if(!empty($voteMessage))<p class="mt-2 text-xs text-slate-500">{{ $voteMessage }}</p>@endif
                        @if(!empty($favoriteMessage))<p class="mt-1 text-xs text-slate-500">{{ $favoriteMessage }}</p>@endif
                        @if(!empty($watchMessage))<p class="mt-1 text-xs text-slate-500">{{ $watchMessage }}</p>@endif
                        @if(!empty($compareMessage))
                            <p class="mt-1 text-xs text-slate-500">{{ $compareMessage }}
                                @if(!empty($compareUrl))
                                    <a href="{{ $compareUrl }}" class="text-brand-600 dark:text-brand-300 font-medium">Open</a>
                                @endif
                            </p>
                        @endif
                    </div>

                    @if($propTools->isNotEmpty())
                        <div class="w-full lg:w-72 shrink-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-3">Replaces / compared to</p>
                            <ul class="space-y-2">
                                @foreach($propTools as $pt)
                                    <li>
                                        <a href="{{ route('alternativesto.show', $pt->slug) }}"
                                            class="flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-800/80 px-3 py-2.5 hover:border-brand-400 dark:hover:border-brand-500 transition group/pt">
                                            @if($pt->logo_url)
                                                <img src="{{ $pt->logo_url }}" alt="" class="h-10 w-10 rounded-xl object-contain bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-700 p-1" loading="lazy" width="40" height="40">
                                            @else
                                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 text-sm font-bold">
                                                    {{ strtoupper(\Illuminate\Support\Str::substr($pt->name, 0, 1)) }}
                                                </span>
                                            @endif
                                            <span class="min-w-0">
                                                <span class="block font-semibold text-slate-900 dark:text-white group-hover/pt:text-brand-600 dark:group-hover/pt:text-brand-300 truncate">{{ $pt->name }}</span>
                                                <span class="block text-xs text-slate-500">View alternatives</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <section class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mb-8">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_stars ?? 0) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">GitHub stars</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->github_forks ?? 0) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Forks</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($metric->open_issues ?? 0) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Open issues</div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 shadow-sm">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($alternative->overall_health_score, 1) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Health score</div>
            </div>
        </section>

        @if(!empty($healthPoints))
            <div class="mb-8">
                <x-health-chart :series="$healthSeries" :points="$healthPoints" :trend="$healthTrend" />
            </div>
        @endif
