<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="mb-6 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-brand-600">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('finder') }}" class="hover:text-brand-600">Alternatives</a>
            <span class="mx-1.5">/</span>
            <span class="text-slate-800 dark:text-slate-200">{{ $tool->name }}</span>
        </nav>

        <header class="rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-10 shadow-sm mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 mb-2">Alternatives to</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ $heading ?? ('Open Source Alternatives to '.$tool->name) }}
            </h1>
            @if($tool->description)
                <div class="mt-4 text-slate-600 dark:text-slate-300 leading-relaxed max-w-3xl prose dark:prose-invert">
                    {!! $tool->description !!}
                </div>
            @endif
            <div class="mt-6 flex flex-wrap gap-3">
                @if($tool->website_url)
                    <a href="{{ $tool->website_url }}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                        Official website
                    </a>
                @endif
                <a href="{{ route('finder', ['tool' => $tool->slug]) }}" class="inline-flex rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    Filter in finder
                </a>
            </div>
            @if(is_array($tool->key_features) && count($tool->key_features))
                <div class="mt-8">
                    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-3">Key features of {{ $tool->name }}</h2>
                    <ul class="flex flex-wrap gap-2">
                        @foreach($tool->key_features as $feature)
                            <li class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium px-3 py-1">
                                {{ is_string($feature) ? $feature : json_encode($feature) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </header>

        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $alternatives->count() }} open-source alternative{{ $alternatives->count() === 1 ? '' : 's' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Ranked by health score</p>
            </div>
            <a href="{{ route('alternatives.compare') }}" class="text-sm font-semibold text-brand-600 hover:underline">Compare →</a>
        </div>

        @if($alternatives->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-10 text-center text-slate-500">
                No published alternatives yet.
                <a href="{{ route('suggest') }}" class="text-brand-600 font-semibold hover:underline">Suggest one</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($alternatives as $alt)
                    <a href="{{ route('alternatives.show', $alt) }}"
                        class="group rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-sm hover:border-brand-300 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600">{{ $alt->name }}</h3>
                            <span class="text-xs font-semibold rounded-full bg-brand-50 dark:bg-brand-600/20 text-brand-700 dark:text-brand-300 px-2 py-0.5">
                                {{ number_format($alt->overall_health_score, 0) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ strip_tags((string) $alt->description) }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs">
                            @if($alt->license_type)
                                <span class="rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-0.5">{{ $alt->license_type }}</span>
                            @endif
                            @if($alt->primary_language)
                                <span class="rounded-full bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300 px-2 py-0.5">{{ $alt->primary_language }}</span>
                            @endif
                            @if($alt->repoMetric)
                                <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-0.5">★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
