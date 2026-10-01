<div class="min-h-screen bg-white dark:bg-slate-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        {{-- Breadcrumb --}}
        <nav class="mb-8 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('finder') }}" class="hover:text-brand-600">Alternatives</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 dark:text-slate-200">{{ $tool->name }}</span>
        </nav>

        {{-- Hero — matches opensourcealternative.to pattern --}}
        <header class="mb-10">
            <div class="flex items-start gap-4 mb-5">
                @if($tool->logo_url)
                    <img src="{{ $tool->logo_url }}" alt="{{ $tool->name }}"
                        class="h-14 w-14 sm:h-16 sm:w-16 rounded-xl object-contain bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1.5 shrink-0"
                        loading="eager" width="64" height="64">
                @else
                    <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-xl bg-brand-50 dark:bg-brand-950 border border-brand-100 dark:border-brand-900 flex items-center justify-center text-xl font-bold text-brand-700 dark:text-brand-300 shrink-0">
                        {{ strtoupper(Str::substr($tool->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                        {{ $heading }}
                    </h1>
                </div>
            </div>

            <p class="text-lg text-slate-600 dark:text-slate-300 font-medium">
                The best
                @if(count($categoryList))
                    {{ implode(', ', array_slice($categoryList, 0, 3)) }}
                @else
                    open-source
                @endif
                tools similar to {{ $tool->name }}
            </p>

            @if($tool->description)
                <div class="mt-5 text-slate-600 dark:text-slate-400 leading-relaxed prose dark:prose-invert max-w-none">
                    {!! Str::limit(strip_tags($tool->description), 400) !!}
                </div>
            @endif

            @if($count > 0)
                <p class="mt-5 text-slate-600 dark:text-slate-400 leading-relaxed">
                    @php $top = $alternatives->first(); @endphp
                    @if($top)
                        <a href="{{ route('alternatives.show', $top) }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">{{ $top->name }}</a>
                        stands out as a leading open-source alternative to {{ $tool->name }}.
                    @endif
                    @if(count($notable) > 1)
                        Notable mentions include
                        @foreach(array_slice($notable, 1) as $i => $name)
                            @php $alt = $alternatives->firstWhere('name', $name); @endphp
                            @if($alt)
                                <a href="{{ route('alternatives.show', $alt) }}" class="font-semibold text-slate-800 dark:text-slate-200 hover:text-brand-600 dark:hover:text-brand-400 hover:underline">{{ $name }}</a>@if($i < count($notable) - 2), @elseif($i === count($notable) - 2) and @endif
                            @endif
                        @endforeach.
                    @endif
                    Explore these alternatives to find tools that match your needs — features, self-hosting, or license.
                </p>
            @endif

            <div class="mt-6 flex flex-wrap gap-3">
                @if($tool->website_url)
                    <a href="{{ $tool->website_url }}" target="_blank" rel="noopener noreferrer"
                        class="text-sm font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 underline-offset-2 hover:underline">
                        Official {{ $tool->name }} website →
                    </a>
                @endif
            </div>
        </header>

        {{-- Full alternative write-ups (OSA.to style) --}}
        @forelse($alternatives as $alt)
            <article class="border-t border-slate-200 dark:border-slate-800 py-10 first:border-t-0 first:pt-0">
                <div class="flex flex-wrap items-baseline justify-between gap-3 mb-3">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                        <a href="{{ route('alternatives.show', $alt) }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                            {{ $alt->name }}
                        </a>
                        @if($alt->primary_language)
                            <span class="text-base font-medium text-slate-400">{{ $alt->primary_language }}</span>
                        @endif
                    </h2>
                    @if($alt->repoMetric && $alt->repoMetric->github_stars)
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-slate-600 dark:text-slate-300 tabular-nums">
                            <svg class="h-4 w-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ number_format($alt->repoMetric->github_stars) }}
                        </span>
                    @endif
                </div>

                @if($alt->description)
                    <div class="text-slate-600 dark:text-slate-300 leading-relaxed mb-4">
                        {!! Str::markdown(strip_tags((string) $alt->description, '<p><br><strong><em><ul><ol><li><a>')) !!}
                    </div>
                @endif

                @php
                    $pros = is_array($alt->pros) ? $alt->pros : [];
                @endphp
                @if(count($pros))
                    <ul class="space-y-1.5 mb-5 text-sm text-slate-700 dark:text-slate-300">
                        @foreach(array_slice($pros, 0, 10) as $pro)
                            <li class="flex gap-2">
                                <span class="text-brand-500 font-bold shrink-0">•</span>
                                <span><strong class="text-slate-900 dark:text-white">{{ is_string($pro) ? (str_contains($pro, ':') ? Str::before($pro, ':') : '') : '' }}</strong>
                                @if(is_string($pro) && str_contains($pro, ':'))
                                    : {{ Str::after($pro, ':') }}
                                @elseif(is_string($pro))
                                    {{ $pro }}
                                @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @if($alt->license_type)
                        <span class="rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-200 px-2.5 py-0.5 text-xs font-semibold">{{ $alt->license_type }}</span>
                    @endif
                    <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2.5 py-0.5 text-xs font-semibold">Health {{ number_format($alt->overall_health_score, 0) }}</span>
                    <a href="{{ route('alternatives.show', $alt) }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                        More about {{ $alt->name }} →
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500">
                No published alternatives yet.
                <a href="{{ route('suggest') }}" class="text-brand-600 font-semibold hover:underline">Suggest one</a>
            </div>
        @endforelse

        <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-800 text-center">
            <a href="{{ route('finder') }}" class="text-sm font-semibold text-brand-600 hover:underline">Browse all open-source alternatives →</a>
        </div>
    </div>
</div>
