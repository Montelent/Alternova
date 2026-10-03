<x-filament-panels::page>
    <div class="space-y-6 text-gray-950 dark:text-gray-100">
        <x-filament::section>
            <x-slot name="heading">What needs you</x-slot>
            <x-slot name="description">
                A simple checklist for new operators. Fix high counts first — drafts stay invisible until published.
            </x-slot>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($this->summary() as $card)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 flex flex-col">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['label'] }}</p>
                            <span @class([
                                'inline-flex min-w-[1.75rem] justify-center rounded-full px-2 py-0.5 text-xs font-bold',
                                'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' => $card['count'] > 0,
                                'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' => $card['count'] === 0,
                            ])>
                                {{ $card['count'] }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400 leading-relaxed flex-1">{{ $card['hint'] }}</p>
                        <a href="{{ $card['href'] }}"
                            class="mt-3 inline-flex text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            {{ $card['cta'] }} →
                        </a>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        @php
            $dupGroups = $this->duplicateRepoGroups();
            $dupPairs = $this->duplicateNamePairs();
        @endphp
        @if(count($dupGroups) > 0 || count($dupPairs) > 0)
            <x-filament::section>
                <x-slot name="heading">Possible duplicates</x-slot>
                <x-slot name="description">
                    Same repository URL is the strongest signal. Similar names are only a hint.
                    <a href="{{ \App\Filament\Pages\DuplicateAlternativesPage::getUrl() }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Open full detector →</a>
                </x-slot>

                @if(count($dupGroups) > 0)
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Same repository</p>
                    <div class="space-y-3 mb-6">
                        @foreach($dupGroups as $group)
                            <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                                <p class="font-mono text-xs text-amber-900 dark:text-amber-200 break-all mb-2">{{ $group['repo'] }}</p>
                                <ul class="space-y-2">
                                    @foreach($group['items'] as $item)
                                        <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 text-sm">
                                            <div>
                                                <span class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</span>
                                                <span class="text-gray-500">· {{ $item['published'] ? 'Published' : 'Draft' }}</span>
                                                @if(!empty($item['tool']))
                                                    <span class="text-gray-500">· vs {{ $item['tool'] }}</span>
                                                @endif
                                            </div>
                                            <a href="{{ $this->editAlternativeUrl($item['id']) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline shrink-0">Edit</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(count($dupPairs) > 0)
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Similar names</p>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($dupPairs as $pair)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                                <div class="min-w-0">
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pair['a']['name'] }}</span>
                                    <span class="text-gray-400 mx-1">↔</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $pair['b']['name'] }}</span>
                                    <span class="ml-2 text-xs font-bold text-amber-700 dark:text-amber-300">{{ number_format($pair['score'], 0) }}%</span>
                                </div>
                                <div class="flex gap-3 shrink-0">
                                    <a href="{{ $this->editAlternativeUrl($pair['a']['id']) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Edit A</a>
                                    <a href="{{ $this->editAlternativeUrl($pair['b']['id']) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Edit B</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">Draft alternatives</x-slot>
                @php $drafts = $this->drafts(); @endphp
                @if($drafts->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No drafts. Nice work.</p>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($drafts as $alt)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $alt->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $alt->proprietaryTool?->name }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <x-filament::button size="sm" color="success" wire:click="publishDraft({{ $alt->id }})">
                                        Publish
                                    </x-filament::button>
                                    <x-filament::button size="sm" color="gray" tag="a"
                                        href="{{ \App\Filament\Resources\OpenSourceAlternativeResource::getUrl('edit', ['record' => $alt]) }}">
                                        Edit
                                    </x-filament::button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Broken links</x-slot>
                @php $broken = $this->broken(); @endphp
                @if($broken->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No broken links recorded.</p>
                @else
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($broken as $alt)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $alt->name }}</p>
                                    <p class="text-xs text-gray-500">
                                        Repo: {{ $alt->repo_reachable ? 'OK' : 'broken' }}
                                        · Site: {{ $alt->website_reachable ? 'OK' : 'broken' }}
                                    </p>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <x-filament::button size="sm" color="warning" wire:click="recheckOne({{ $alt->id }})">
                                        Recheck
                                    </x-filament::button>
                                    <x-filament::button size="sm" color="gray" tag="a"
                                        href="{{ \App\Filament\Resources\OpenSourceAlternativeResource::getUrl('edit', ['record' => $alt]) }}">
                                        Edit
                                    </x-filament::button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>

            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">Published with health score 0</x-slot>
                <x-slot name="description">Usually means metrics were never synced. Needs a GitHub token for best results (Integrations).</x-slot>
                @php $zeros = $this->zeroHealth(); @endphp
                @if($zeros->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">All published alternatives have a health score.</p>
                @else
                    <div class="mb-3">
                        <x-filament::button size="sm" color="success" wire:click="syncAllZeroHealth" icon="heroicon-o-arrow-path">
                            Sync all on this list
                        </x-filament::button>
                    </div>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($zeros as $alt)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $alt->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $alt->repo_url }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 shrink-0">
                                    <x-filament::button size="sm" color="success" wire:click="syncOne({{ $alt->id }})">
                                        Sync metrics
                                    </x-filament::button>
                                    <x-filament::button size="sm" color="gray" tag="a"
                                        href="{{ \App\Filament\Resources\OpenSourceAlternativeResource::getUrl('edit', ['record' => $alt]) }}">
                                        Edit
                                    </x-filament::button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>
        </div>

        @if($lastOutput)
            <x-filament::section>
                <x-slot name="heading">Last output</x-slot>
                <pre class="text-xs whitespace-pre-wrap font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 p-4 rounded-xl overflow-x-auto">{{ $lastOutput }}</pre>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
