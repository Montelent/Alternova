<div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <x-ad-slot slot="domain_page" class="mb-6" />
        <div class="text-center mb-8 sm:mb-10">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-slate-900 dark:text-white tracking-tight">Domain Name Idea Combinator</h1>
            <p class="mt-3 text-base sm:text-lg text-slate-600 dark:text-slate-400 max-w-2xl mx-auto">
                Generate brandable domain ideas from seed keywords, score them, and check availability in real time.
            </p>
        </div>

        @if($errorMessage)
            <div class="mb-6 rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-300 text-sm px-4 py-3">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 p-4 sm:p-6 mb-8 space-y-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Seed Keywords</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($keywords as $kw)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-brand-100 text-brand-800 dark:bg-brand-600/30 dark:text-brand-200">
                            {{ $kw }}
                            <button wire:click="removeKeyword('{{ $kw }}')" type="button" class="ml-1.5 opacity-70 hover:opacity-100">&times;</button>
                        </span>
                    @endforeach
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input wire:model="keywordInput" wire:keydown.enter.prevent="addKeyword" type="text"
                        placeholder="Add keyword and press Enter"
                        class="flex-1 rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-brand-500 focus:border-brand-500 text-sm min-h-[44px]">
                    <button wire:click="addKeyword" type="button"
                        class="px-4 py-2.5 bg-brand-600 text-white rounded-xl text-sm font-medium hover:bg-brand-700 min-h-[44px]">Add</button>
                </div>
            </div>

            @include('livewire.partials.domain-affixes')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">TLDs</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($availableTlds as $tld)
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-sm cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                            <input type="checkbox" wire:model="selectedTlds" value="{{ $tld }}" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            .{{ $tld }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Max length ({{ $maxLength }})</label>
                    <input type="range" wire:model.live="maxLength" min="4" max="25" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Syllable limit ({{ $maxSyllables }})</label>
                    <input type="range" wire:model.live="maxSyllables" min="1" max="6" class="w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Min brandability ({{ $minBrandability }})</label>
                    <input type="range" wire:model.live="minBrandability" min="0" max="90" class="w-full">
                </div>
            </div>

            <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                <button wire:click="generate" wire:loading.attr="disabled"
                    class="px-6 py-3 bg-brand-600 text-white rounded-xl text-sm font-semibold hover:bg-brand-700 disabled:opacity-60 min-h-[44px]">
                    <span wire:loading.remove wire:target="generate">Generate Domains</span>
                    <span wire:loading wire:target="generate">Generating…</span>
                </button>
                @if(collect($results)->where('status', 'available')->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="exportCsv" class="px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 min-h-[44px]">Export CSV</button>
                        <button wire:click="exportTxt" class="px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl text-sm hover:bg-slate-50 dark:hover:bg-slate-800 min-h-[44px]">Export TXT</button>
                    </div>
                @endif
            </div>
        </div>

        @if(count($saved))
            <div class="mb-8 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Saved domains</h2>
                    <div class="flex gap-3">
                        <button wire:click="exportSavedTxt" class="text-xs font-semibold text-brand-600 hover:underline">Export</button>
                        <button wire:click="clearSaved" wire:confirm="Clear all saved domains?" class="text-xs font-semibold text-red-600 hover:underline">Clear</button>
                    </div>
                </div>
                <ul class="flex flex-wrap gap-2">
                    @foreach($saved as $s)
                        <li class="inline-flex items-center gap-2 rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-mono">
                            {{ $s['domain'] }}
                            <button type="button" wire:click="toggleSave('{{ $s['domain'] }}')" class="text-slate-400 hover:text-red-500">&times;</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(count($results))
            @if($isChecking)
                <div class="mb-4 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400" wire:poll.750ms="checkNextBatch">
                    <span class="inline-block h-3.5 w-3.5 rounded-full border-2 border-brand-500 border-t-transparent animate-spin"></span>
                    Checking availability in batches…
                </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                @foreach($results as $item)
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 flex flex-col justify-between min-h-[7rem]">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-mono text-sm font-medium text-slate-900 dark:text-white break-all">{{ $item['domain'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">Brandability: {{ $item['score'] }}</div>
                            </div>
                            <button type="button"
                                wire:click="toggleSave('{{ $item['domain'] }}', {{ (int) $item['score'] }}, '{{ $item['status'] }}')"
                                class="text-lg leading-none shrink-0 {{ $this->isSaved($item['domain']) ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400' }}"
                                title="Save domain">★</button>
                        </div>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                            @if(in_array($item['status'], ['pending', 'checking'], true))
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Checking…</span>
                            @elseif($item['status'] === 'available')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">Available</span>
                            @elseif($item['status'] === 'taken')
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">Taken</span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200">Unknown</span>
                            @endif

                            @if($item['status'] === 'available' && !empty($item['affiliate']))
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <a href="{{ $item['affiliate']['namecheap'] ?? '#' }}" target="_blank" rel="noopener sponsored" class="text-brand-600 hover:underline">Namecheap</a>
                                    <a href="{{ $item['affiliate']['porkbun'] ?? '#' }}" target="_blank" rel="noopener sponsored" class="text-brand-600 hover:underline">Porkbun</a>
                                    <a href="{{ $item['affiliate']['godaddy'] ?? '#' }}" target="_blank" rel="noopener sponsored" class="text-brand-600 hover:underline">GoDaddy</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <x-ad-slot slot="between_list" class="my-8" />
        @endif
    </div>
</div>
