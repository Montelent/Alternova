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
