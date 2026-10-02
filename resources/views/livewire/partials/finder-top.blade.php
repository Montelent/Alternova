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
                <a href="{{ route('browse.type', 'categories') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Browse hubs</a>
                <span class="text-slate-300 dark:text-slate-600">·</span>
                <a href="{{ route('alternatives.compare') }}" class="font-semibold text-brand-600 dark:text-brand-400 hover:underline">Compare tools</a>
                <span class="text-slate-300 dark:text-slate-600">·</span>
                <a href="{{ route('suggest') }}" class="font-semibold text-slate-500 dark:text-slate-400 hover:underline">Suggest one</a>
            </div>
        </div>

        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 text-center mb-2">Categories</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($chipCategories as $cat)
                    <button type="button" wire:click="toggleCategory({{ json_encode($cat) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($cat, $categories) ? 'bg-brand-600 text-white border-brand-600' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        </div>

        @if(count($availableLicenses))
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 text-center mb-2">License types</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($availableLicenses as $lic)
                    <button type="button" wire:click="toggleLicense({{ json_encode($lic) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($lic, $licenses) ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
                        {{ $lic }}
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        @if(count($availableLanguages))
        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 text-center mb-2">Languages</p>
            <div class="flex flex-wrap gap-2 justify-center">
                @foreach($availableLanguages as $lang)
                    <button type="button" wire:click="toggleLanguage({{ json_encode($lang) }})"
                        class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($lang, $languages) ? 'bg-sky-600 text-white border-sky-600' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' }}">
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
                        class="block w-full px-4 py-3 border border-slate-300 dark:border-slate-700 bg-white text-slate-900 dark:bg-slate-800 dark:text-white rounded-xl focus:ring-2 focus:ring-brand-500 text-sm">
                </div>
                <div class="flex flex-wrap gap-3 items-center">
                    <select wire:model.live="toolSlug"
                        class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white text-slate-900 dark:bg-slate-800 dark:text-white text-sm max-w-[12rem]">
                        <option value="">All products</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->slug }}">{{ $tool->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="sort" class="rounded-xl border border-slate-300 dark:border-slate-700 bg-white text-slate-900 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="health">Best Health Score</option>
                        <option value="stars">Most Stars</option>
                        <option value="votes">Most Votes</option>
                        <option value="newest">Newest</option>
                        <option value="name">Name A-Z</option>
                    </select>
                    @if($search || count($licenses) || count($difficulties) || count($categories) || count($languages) || $toolSlug)
                        <button type="button" wire:click="clearFilters" class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">Clear</button>
                    @endif
                </div>
            </div>
        </div>
