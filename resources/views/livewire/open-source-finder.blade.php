<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{-- Header --}}
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight">
                Open Source Alternative Finder
            </h1>
            <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">
                Discover high-quality, self-hostable open-source alternatives to popular proprietary tools.
            </p>
        </div>

        {{-- Search & Filters --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="search"
                            placeholder="Search tools, languages, features..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        >
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    {{-- License Multi-select --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            License
                            @if(count($licenses))
                                <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ count($licenses) }}
                                </span>
                            @endif
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute z-20 mt-2 w-56 rounded-xl bg-white shadow-lg border border-gray-200 py-2">
                            @foreach($availableLicenses as $license)
                                <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" wire:model.live="licenses" value="{{ $license }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-700">{{ $license }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Difficulty --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-xl bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Difficulty
                            @if(count($difficulties))
                                <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ count($difficulties) }}
                                </span>
                            @endif
                            <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-cloak
                            class="absolute z-20 mt-2 w-48 rounded-xl bg-white shadow-lg border border-gray-200 py-2">
                            @foreach($difficultyLabels as $value => $label)
                                <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" wire:model.live="difficulties" value="{{ $value }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort --}}
                    <select wire:model.live="sort"
                        class="rounded-xl border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="health">Best Health Score</option>
                        <option value="stars">Most Stars</option>
                        <option value="newest">Newest</option>
                        <option value="name">Name A-Z</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Results Grid --}}
        <div wire:loading.class="opacity-50" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($alternatives as $alt)
                <a href="{{ route('alternatives.show', $alt) }}"
                    class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition">
                                    {{ $alt->name }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-0.5">
                                    Alternative to <span class="font-medium text-gray-700">{{ $alt->proprietaryTool?->name }}</span>
                                </p>
                            </div>
                            <div class="flex items-center gap-1 text-amber-500">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700">{{ number_format($alt->overall_health_score, 1) }}</span>
                            </div>
                        </div>

                        <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                            {{ $alt->description }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($alt->license_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $alt->license_type }}
                                </span>
                            @endif
                            @if($alt->primary_language)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $alt->primary_language }}
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Difficulty {{ $alt->self_host_difficulty }}/5
                            </span>
                        </div>

                        @if($alt->repoMetric)
                            <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ number_format($alt->repoMetric->github_stars) }}
                                </span>
                                <span>{{ number_format($alt->repoMetric->github_forks) }} forks</span>
                            </div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-16">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No alternatives found</h3>
                    <p class="mt-2 text-sm text-gray-500">Try adjusting your search or filters.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $alternatives->links() }}
        </div>
    </div>
</div>
