<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <div class="text-center mb-8">
            <p class="text-sm font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-2">Community signal</p>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Trending this week</h1>
            <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm max-w-md mx-auto">
                Ranked by upvotes in the selected window — not all-time popularity.
            </p>
            <div class="mt-5 flex justify-center gap-2">
                @foreach([7 => '7 days', 14 => '14 days', 30 => '30 days'] as $d => $label)
                    <button type="button" wire:click="setDays({{ $d }})"
                        class="rounded-full px-3 py-1 text-xs font-semibold transition
                        {{ $days === $d
                            ? 'bg-brand-600 text-white'
                            : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-brand-400' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        @if($rows->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-12 text-center text-slate-500">
                No votes in the last {{ $days }} days yet.
                <a href="{{ route('leaderboard') }}" class="block mt-3 text-brand-600 dark:text-brand-300 font-medium hover:underline">See all-time rankings →</a>
            </div>
        @else
            <ol class="space-y-3">
                @foreach($rows as $i => $alt)
                    <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5 shadow-sm flex items-start gap-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 dark:bg-amber-500/15 text-amber-800 dark:text-amber-200 text-sm font-bold">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('alternatives.show', $alt) }}" class="text-lg font-semibold text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-300">{{ $alt->name }}</a>
                                    @if($alt->proprietaryTool)
                                        <p class="text-sm text-slate-500">vs {{ $alt->proprietaryTool->name }}</p>
                                    @endif
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-amber-600 dark:text-amber-400">▲ {{ $alt->period_votes }}</p>
                                    <p class="text-xs text-slate-400">in {{ $days }}d</p>
                                </div>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                <span>Health {{ number_format($alt->overall_health_score, 1) }}</span>
                                @if($alt->license_type)
                                    <span>· {{ $alt->license_type }}</span>
                                @endif
                                @if($alt->repoMetric)
                                    <span>· ★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        <p class="mt-10 text-center text-sm text-slate-500">
            <a href="{{ route('leaderboard') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">Full leaderboards</a>
            ·
            <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">Browse all</a>
        </p>
    </div>
</div>
