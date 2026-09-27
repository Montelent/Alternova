<div class="min-h-screen bg-gray-50" x-data="{ checking: false }"
    @domains-generated.window="checking = true; $wire.checkNextBatch()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight">Domain Name Idea Combinator</h1>
            <p class="mt-3 text-lg text-gray-600 max-w-2xl mx-auto">
                Generate brandable domain ideas from seed keywords, score them, and check availability in real time.
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8 space-y-6">
            {{-- Keywords --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Seed Keywords</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    @foreach($keywords as $kw)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800">
                            {{ $kw }}
                            <button wire:click="removeKeyword('{{ $kw }}')" type="button" class="ml-1.5 text-indigo-600 hover:text-indigo-900">&times;</button>
                        </span>
                    @endforeach
                </div>
                <div class="flex gap-2">
                    <input wire:model="keywordInput" wire:keydown.enter.prevent="addKeyword" type="text"
                        placeholder="Add keyword and press Enter"
                        class="flex-1 rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <button wire:click="addKeyword" type="button"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">Add</button>
                </div>
            </div>

            {{-- TLDs --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TLDs</label>
                <div class="flex flex-wrap gap-3">
                    @foreach($availableTlds as $tld)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selectedTlds" value="{{ $tld }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">.{{ $tld }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Sliders --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Length: {{ $maxLength }}</label>
                    <input type="range" wire:model.live="maxLength" min="4" max="25" class="w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Syllables: {{ $maxSyllables }}</label>
                    <input type="range" wire:model.live="maxSyllables" min="1" max="6" class="w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Brandability: {{ $minBrandability }}</label>
                    <input type="range" wire:model.live="minBrandability" min="0" max="90" class="w-full">
                </div>
            </div>

            <div class="flex justify-between items-center">
                <button wire:click="generate" wire:loading.attr="disabled"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="generate">Generate Domains</span>
                    <span wire:loading wire:target="generate">Generating...</span>
                </button>

                @if(count($results))
                    <div class="flex gap-2">
                        <button wire:click="exportCsv" class="px-4 py-2 border border-gray-300 rounded-xl text-sm hover:bg-gray-50">Export CSV</button>
                        <button wire:click="exportTxt" class="px-4 py-2 border border-gray-300 rounded-xl text-sm hover:bg-gray-50">Export TXT</button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Results --}}
        @if(count($results))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                wire:poll.2s="checkNextBatch">
                @foreach($results as $item)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col justify-between">
                        <div>
                            <div class="font-mono text-sm font-medium text-gray-900 break-all">{{ $item['domain'] }}</div>
                            <div class="mt-1 text-xs text-gray-500">Brandability: {{ $item['score'] }}</div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            @if($item['status'] === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Checking...
                                </span>
                            @elseif($item['status'] === 'available')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Available
                                </span>
                            @elseif($item['status'] === 'taken')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Taken
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Unknown
                                </span>
                            @endif

                            @if($item['status'] === 'available' && !empty($item['affiliate']))
                                <div class="flex gap-1">
                                    <a href="{{ $item['affiliate']['namecheap'] }}" target="_blank" rel="noopener sponsored"
                                        class="text-xs text-indigo-600 hover:underline">Namecheap</a>
                                    <a href="{{ $item['affiliate']['porkbun'] }}" target="_blank" rel="noopener sponsored"
                                        class="text-xs text-indigo-600 hover:underline">Porkbun</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
