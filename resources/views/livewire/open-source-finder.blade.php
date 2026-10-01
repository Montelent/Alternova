<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center mb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">
                Open Source Alternative Finder
            </h1>
            <p class="mt-3 text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                Discover high-quality, self-hostable open-source alternatives to popular proprietary tools.
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm">
                <a href="{{ route('alternatives.compare') }}" class="font-semibold text-brand-600 hover:underline">Compare tools</a>
                <span class="text-slate-300">·</span>
                <a href="{{ route('suggest') }}" class="font-semibold text-slate-500 hover:underline">Suggest one</a>
            </div>
        </div>

        {{-- Categories --}}
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 text-center mb-2">Categories</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($chipCategories as $cat)
                    <button type="button" wire:click="toggleCategory({{ json_encode($cat) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($cat, $categories) ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Licenses --}}
        @if(count($availableLicenses))
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 text-center mb-2">License types</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($availableLicenses as $lic)
                    <button type="button" wire:click="toggleLicense({{ json_encode($lic) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($lic, $licenses) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                        {{ $lic }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Languages --}}
        @if(count($availableLanguages))
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 text-center mb-2">Languages</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($availableLanguages as $lang)
                    <button type="button" wire:click="toggleLanguage({{ json_encode($lang) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($lang, $languages) ? 'bg-sky-600 text-white border-sky-600' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                        {{ $lang }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-5 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <input wire:model.live.debounce.300ms="search" type="search"
                        placeholder="Search tools, languages, features..."
                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
                <div class="flex flex-wrap gap-3 items-center">
                    <select wire:model.live="toolSlug"
                        class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm max-w-[12rem]">
                        <option value="">All products</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->slug }}">{{ $tool->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="sort" class="rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="health">Best Health Score</option>
                        <option value="stars">Most Stars</option>
                        <option value="votes">Most Votes</option>
                        <option value="newest">Newest</option>
                        <option value="name">Name A-Z</option>
                    </select>
                    @if($search || count($licenses) || count($difficulties) || count($categories) || count($languages) || $toolSlug)
                        <button type="button" wire:click="clearFilters" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">Clear</button>
                    @endif
                </div>
            </div>
        </div>

        <div wire:loading.class="opacity-50" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($alternatives as $alt)
                <a href="{{ route('alternatives.show', $alt) }}"
                    class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-300 transition-all overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-900 dark:text-white group-hover:text-brand-600 transition">{{ $alt->name }}</h3>
                                <p class="text-sm text-slate-500 mt-0.5">
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
                            <div class="mt-4 flex items-center gap-4 text-sm text-slate-500">
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
                    <button type="button" wire:click="clearFilters" class="mt-4 text-sm font-semibold text-brand-600">Clear filters</button>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $alternatives->links() }}
        </div>
    </div>
</div>
