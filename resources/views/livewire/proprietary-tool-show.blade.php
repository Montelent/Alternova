<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Home</a>
            <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
            <a href="{{ route('finder') }}" class="hover:text-brand-600 dark:hover:text-brand-300">Alternatives</a>
            <span class="mx-1.5 text-slate-300 dark:text-slate-600">/</span>
            <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $tool->name }}</span>
        </nav>

        {{-- Hero --}}
        <header class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 lg:p-10 shadow-sm mb-8 sm:mb-10">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5 sm:gap-6">
                @if($tool->logo_url)
                    <img src="{{ $tool->logo_url }}" alt="{{ $tool->name }} logo"
                        class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl object-contain bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-2 shrink-0"
                        width="80" height="80" loading="eager">
                @else
                    <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-2xl bg-brand-50 dark:bg-brand-950/50 border border-brand-100 dark:border-brand-900 flex items-center justify-center text-2xl font-bold text-brand-700 dark:text-brand-300 shrink-0" aria-hidden="true">
                        {{ strtoupper(\Illuminate\Support\Str::substr($tool->name, 0, 1)) }}
                    </div>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400 mb-2">
                        Alternatives directory
                    </p>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                        {{ $heading }}
                    </h1>
                    <p class="mt-3 text-base sm:text-lg text-slate-600 dark:text-slate-300 font-medium">
                        {{ $kicker }}
                    </p>
                </div>
            </div>

            <div class="mt-6 space-y-4 text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed max-w-3xl">
                <p>{{ $intro }}</p>
                <p>{{ $body }}</p>
            </div>

            @if(is_array($tool->key_features) && count($tool->key_features))
                <div class="mt-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 mb-2">What {{ $tool->name }} is known for</p>
                    <ul class="flex flex-wrap gap-2">
                        @foreach($tool->key_features as $feature)
                            <li class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium px-3 py-1">
                                {{ is_string($feature) ? $feature : json_encode($feature) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if($tool->website_url)
                    <a href="{{ $tool->website_url }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        Official website
                    </a>
                @endif
                <a href="{{ route('finder', ['tool' => $tool->slug]) }}"
                    class="inline-flex items-center rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-500 transition">
                    Open in finder
                </a>
            </div>
        </header>

        {{-- Grid of alternatives --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white">
                    {{ $count }} alternative{{ $count === 1 ? '' : 's' }} to explore
                </h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sorted by health score. Tap a card for the full profile.</p>
            </div>
            <a href="{{ route('alternatives.compare') }}" class="text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline shrink-0">
                Compare tools
            </a>
        </div>

        @if($alternatives->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-10 sm:p-14 text-center">
                <p class="text-slate-600 dark:text-slate-400">No published alternatives for {{ $tool->name }} yet.</p>
                <a href="{{ route('suggest') }}" class="mt-4 inline-flex font-semibold text-brand-600 dark:text-brand-400 hover:underline">Suggest one</a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach($alternatives as $alt)
                    <a href="{{ route('alternatives.show', $alt) }}"
                        class="group flex flex-col rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm hover:border-brand-300 dark:hover:border-brand-600 hover:shadow-md transition h-full">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-400 transition leading-snug">
                                {{ $alt->name }}
                            </h3>
                            <span class="shrink-0 rounded-full bg-brand-50 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 text-xs font-bold px-2.5 py-1 tabular-nums">
                                {{ number_format($alt->overall_health_score, 0) }}
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 line-clamp-3 flex-1">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $alt->description), 140) }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-200 text-[11px] font-semibold px-2.5 py-0.5">
                                    {{ $alt->license_type }}
                                </span>
                            @endif
                            @if($alt->primary_language)
                                <span class="rounded-full bg-sky-50 dark:bg-sky-950/50 text-sky-800 dark:text-sky-200 text-[11px] font-semibold px-2.5 py-0.5">
                                    {{ $alt->primary_language }}
                                </span>
                            @endif
                            @if($alt->repoMetric && $alt->repoMetric->github_stars)
                                <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-semibold px-2.5 py-0.5">
                                    ★ {{ number_format($alt->repoMetric->github_stars) }}
                                </span>
                            @endif
                        </div>

                        <span class="mt-4 text-sm font-semibold text-brand-600 dark:text-brand-400 group-hover:underline">
                            View profile
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-10 sm:mt-12 text-center">
            <a href="{{ route('finder') }}" class="text-sm font-semibold text-slate-500 hover:text-brand-600 dark:hover:text-brand-400 transition">
                Browse all open source alternatives
            </a>
        </div>
    </div>
</div>
