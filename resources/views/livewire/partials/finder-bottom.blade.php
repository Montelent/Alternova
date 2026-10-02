        <div wire:loading.class="opacity-50" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($alternatives as $alt)
                <a href="{{ route('alternatives.show', $alt) }}"
                    class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-300 transition-all overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-3 min-w-0">
                                @include('components.alternative-logo', ['alt' => $alt])
                                <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 transition">{{ $alt->name }}</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                                    Alternative to
                                    @php
                                        $toolsList = $alt->relationLoaded('proprietaryTools') && $alt->proprietaryTools->isNotEmpty()
                                            ? $alt->proprietaryTools
                                            : collect($alt->proprietaryTool ? [$alt->proprietaryTool] : []);
                                    @endphp
                                    @forelse($toolsList as $t)
                                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $t->name }}</span>@if(!$loop->last), @endif
                                    @empty
                                        <span class="font-medium text-slate-700 dark:text-slate-300">proprietary tools</span>
                                    @endforelse
                                </p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-amber-600 shrink-0">{{ number_format($alt->overall_health_score, 1) }}</span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ strip_tags((string) $alt->description) }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">{{ $alt->license_type }}</span>
                            @endif
                            @if($alt->primary_language)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">{{ $alt->primary_language }}</span>
                            @endif
                        </div>
                        @if($alt->repoMetric)
                            <div class="mt-4 flex items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                                <span>★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                                @if($alt->votes_count)
                                    <span>{{ $alt->votes_count }} votes</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">No alternatives found</h3>
                    <button type="button" wire:click="clearFilters" class="mt-4 text-sm font-semibold text-brand-600 dark:text-brand-400">Clear filters</button>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $alternatives->links() }}
        </div>
    </div>
</div>
