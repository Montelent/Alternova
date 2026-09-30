<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-2">Rankings</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">Open-source leaderboards</h1>
            <p class="mt-3 text-slate-600 dark:text-slate-400 max-w-xl mx-auto">
                Health scores, community votes, GitHub stars, and rising projects from the Alternova catalog.
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 mb-8">
            @foreach([
                'health' => 'Best health',
                'votes' => 'Most votes',
                'stars' => 'Most stars',
                'rising' => 'Rising',
            ] as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')"
                    class="rounded-full px-4 py-2 text-sm font-semibold border transition
                        {{ $tab === $key
                            ? 'bg-brand-600 text-white border-brand-600'
                            : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:border-brand-400' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <ol class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($rows as $i => $alt)
                    <li>
                        <a href="{{ route('alternatives.show', $alt) }}"
                            class="flex items-center gap-4 px-4 sm:px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition">
                            <span class="w-8 shrink-0 text-center text-lg font-bold {{ $i < 3 ? 'text-brand-600 dark:text-brand-300' : 'text-slate-400' }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $alt->name }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                    @if($alt->proprietaryTool)
                                        vs {{ $alt->proprietaryTool->name }}
                                    @endif
                                    @if($alt->license_type)
                                        · {{ $alt->license_type }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0 text-sm">
                                @if($tab === 'votes')
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($alt->votes_count) }}</span>
                                    <span class="block text-xs text-slate-400">votes</span>
                                @elseif($tab === 'stars')
                                    <span class="font-semibold text-slate-900 dark:text-white">★ {{ number_format($alt->repoMetric?->github_stars ?? 0) }}</span>
                                    <span class="block text-xs text-slate-400">stars</span>
                                @else
                                    <span class="font-semibold text-amber-600 dark:text-amber-400">{{ number_format($alt->overall_health_score, 1) }}</span>
                                    <span class="block text-xs text-slate-400">health</span>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-6 py-12 text-center text-slate-500">No published alternatives yet.</li>
                @endforelse
            </ol>
        </div>

        <p class="mt-8 text-center text-sm text-slate-500">
            <a href="{{ route('finder') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">Browse all alternatives →</a>
        </p>
    </div>
</div>
