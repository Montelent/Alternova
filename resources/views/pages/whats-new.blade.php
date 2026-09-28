@component('layouts.app', ['title' => $title, 'description' => $description])
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 mb-2">Digest</p>
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">What's new</h1>
    <p class="mt-3 text-slate-600 dark:text-slate-400 max-w-2xl">
        Recently published open-source alternatives, grouped by week. Subscribe via
        <a href="{{ url('/feed') }}" class="font-semibold text-brand-600 hover:underline">RSS</a>
        for updates in your reader.
    </p>

    <div class="mt-12 space-y-12">
        @forelse($groups as $weekStart => $alts)
            <section>
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">
                    Week of {{ \Carbon\Carbon::parse($weekStart)->format('M j, Y') }}
                </h2>
                <ul class="space-y-4">
                    @foreach($alts as $alt)
                        <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <a href="{{ route('alternatives.show', $alt) }}" class="text-lg font-semibold text-slate-900 dark:text-white hover:text-brand-600">
                                        {{ $alt->name }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Alternative to
                                        @if($alt->proprietaryTool)
                                            <a href="{{ route('tools.show', $alt->proprietaryTool) }}" class="font-medium text-slate-700 dark:text-slate-300 hover:underline">
                                                {{ $alt->proprietaryTool->name }}
                                            </a>
                                        @else
                                            proprietary tools
                                        @endif
                                        · {{ optional($alt->created_at)->diffForHumans() }}
                                    </p>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ $alt->description }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0 text-xs">
                                    <span class="rounded-full bg-brand-50 dark:bg-brand-600/20 text-brand-700 dark:text-brand-300 px-2.5 py-1 font-semibold">
                                        {{ number_format($alt->overall_health_score, 0) }}
                                    </span>
                                    @if($alt->license_type)
                                        <span class="rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2.5 py-1">{{ $alt->license_type }}</span>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="text-slate-500">No published alternatives yet.</p>
        @endforelse
    </div>

    <div class="mt-12 text-center">
        <a href="{{ route('finder') }}" class="text-sm font-semibold text-brand-600 hover:underline">Browse full catalog →</a>
    </div>
</div>
@endcomponent
