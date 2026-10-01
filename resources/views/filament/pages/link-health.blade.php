<x-filament-panels::page>
    @php($stats = $this->stats())

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-rose-200 dark:border-rose-900/40 bg-rose-50 dark:bg-rose-950/30 p-4">
            <p class="text-xs font-semibold uppercase text-rose-700 dark:text-rose-300">Broken links</p>
            <p class="mt-1 text-2xl font-bold text-rose-900 dark:text-rose-100">{{ $stats['broken'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Checked at least once</p>
            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['checked'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Never checked</p>
            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $stats['never'] }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <x-filament::button wire:click="recheckAllBroken" color="warning" icon="heroicon-o-arrow-path"
            wire:confirm="Recheck every alternative currently marked broken?">
            Recheck all broken
        </x-filament::button>
        <a href="{{ \App\Filament\Pages\CronSettingsPage::getUrl() }}" class="inline-flex items-center text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline">
            Schedule automatic checks →
        </a>
    </div>

    <x-filament::section>
        <x-slot name="heading">Alternatives with unreachable links</x-slot>
        <x-slot name="description">Repo or website returned unreachable on the last check. Fix the URL on the alternative, then recheck.</x-slot>

        @php($rows = $this->brokenAlternatives())

        @if($rows->isEmpty())
            <p class="text-sm text-gray-700 dark:text-gray-300">No broken links recorded. Run a link check from Cron settings or System tools after cron is enabled.</p>
        @else
            <div class="overflow-x-auto -mx-2 sm:mx-0">
                <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100 min-w-[640px]">
                    <thead class="text-xs uppercase text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="py-2 pr-3">Name</th>
                            <th class="py-2 pr-3">Repo</th>
                            <th class="py-2 pr-3">Website</th>
                            <th class="py-2 pr-3">Checked</th>
                            <th class="py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rows as $alt)
                            <tr>
                                <td class="py-3 pr-3 font-medium">
                                    <a href="{{ \App\Filament\Resources\OpenSourceAlternativeResource::getUrl('edit', ['record' => $alt]) }}" class="text-primary-600 dark:text-primary-400 hover:underline">
                                        {{ $alt->name }}
                                    </a>
                                </td>
                                <td class="py-3 pr-3">
                                    @if($alt->repo_reachable === false)
                                        <span class="text-rose-700 dark:text-rose-300 font-semibold">Down</span>
                                    @elseif($alt->repo_reachable === true)
                                        <span class="text-emerald-700 dark:text-emerald-300">OK</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3">
                                    @if($alt->website_reachable === false)
                                        <span class="text-rose-700 dark:text-rose-300 font-semibold">Down</span>
                                    @elseif($alt->website_reachable === true)
                                        <span class="text-emerald-700 dark:text-emerald-300">OK</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-3 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $alt->links_checked_at ? $alt->links_checked_at->diffForHumans() : 'Never' }}
                                </td>
                                <td class="py-3">
                                    <x-filament::button size="sm" color="gray" wire:click="recheck({{ $alt->id }})">
                                        Recheck
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>

    @if($lastOutput)
        <x-filament::section class="mt-6">
            <x-slot name="heading">Last output</x-slot>
            <pre class="text-xs whitespace-pre-wrap font-mono text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 p-4 rounded-xl">{{ $lastOutput }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
