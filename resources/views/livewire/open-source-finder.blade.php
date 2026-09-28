<div class="min-h-screen bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">
                Open Source Alternative Finder
            </h1>
            <p class="mt-3 text-lg text-slate-600 max-w-2xl mx-auto">
                Discover high-quality, self-hostable open-source alternatives to popular proprietary tools.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm">
                <a href="{{ route('alternatives.compare') }}" class="font-semibold text-brand-600 hover:underline">Compare tools</a>
                <span class="text-slate-300">·</span>
                <a href="{{ url('/feed') }}" class="font-semibold text-slate-500 hover:underline">RSS feed</a>
                <span class="text-slate-300">·</span>
                <a href="{{ route('suggest') }}" class="font-semibold text-slate-500 hover:underline">Suggest one</a>
            </div>
        </div>

        {{-- Categories --}}
        <div class="mb-6 flex flex-wrap gap-2 justify-center">
            @foreach($chipCategories as $cat)
                <button type="button"
                    wire:click="toggleCategory({{ json_encode($cat) }})"
                    class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                        {{ in_array($cat, $categories) ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-slate-600 border-slate-200 hover:border-brand-300' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            placeholder="Search tools, languages, features..."
                            class="block w-full pl-10 pr-3 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm"
                        >
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-xl bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">
                            License
                            @if(count($licenses))
                                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">{{ count($licenses) }}</span>
                            @endif
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute z-20 mt-2 w-56 rounded-xl bg-white shadow-lg border border-slate-200 py-2">
                            @foreach($availableLicenses as $license)
                                <label class="flex items-center px-4 py-2 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model.live="licenses" value="{{ $license }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ $license }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-xl bg-white text-sm font-medium text-slate-700 hover:bg-slate-50">
                            Difficulty
                            @if(count($difficulties))
                                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-brand-100 text-brand-800">{{ count($difficulties) }}</span>
                            @endif
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute z-20 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-200 py-2">
                            @foreach($difficultyLabels as $value => $label)
                                <label class="flex items-center px-4 py-2 hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" wire:model.live="difficulties" value="{{ $value }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                    <span class="ml-3 text-sm text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <select wire:model.live="sort" class="rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="health">Best Health Score</option>
                        <option value="stars">Most Stars</option>
                        <option value="newest">Newest</option>
                        <option value="name">Name A-Z</option>
                    </select>

                    @if($search || count($licenses) || count($difficulties) || count($categories))
                        <button type="button" wire:click="clearFilters" class="text-sm font-medium text-slate-500 hover:text-slate-800">
                            Clear
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div wire:loading.class="opacity-50" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($alternatives as $alt)
                <a href="{{ route('alternatives.show', $alt) }}"
                    class="group bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-brand-300 transition-all duration-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900 group-hover:text-brand-600 transition">{{ $alt->name }}</h3>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    Alternative to <span class="font-medium text-slate-700">{{ $alt->proprietaryTool?->name }}</span>
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-amber-600 shrink-0">{{ number_format($alt->overall_health_score, 1) }}</span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 line-clamp-2">{{ $alt->description }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">{{ $alt->license_type }}</span>
                            @endif
                            @if($alt->primary_language)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">{{ $alt->primary_language }}</span>
                            @endif
                            @foreach($alt->tags->where('type', 'category')->take(2) as $tag)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-800">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                        @if($alt->repoMetric)
                            <div class="mt-4 flex items-center gap-4 text-sm text-slate-500">
                                <span>★ {{ number_format($alt->repoMetric->github_stars) }}</span>
                                <span>{{ number_format($alt->repoMetric->github_forks) }} forks</span>
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <h3 class="text-lg font-medium text-slate-900">No alternatives found</h3>
                    <p class="mt-2 text-sm text-slate-500">Try adjusting your search or filters.</p>
                    <button type="button" wire:click="clearFilters" class="mt-4 text-sm font-semibold text-brand-600">Clear filters</button>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $alternatives->links() }}
        </div>
    </div>
</div>
