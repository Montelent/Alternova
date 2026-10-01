<x-filament-panels::page>
    @php($progress = $this->progress())

    <x-filament::section class="mb-6">
        <x-slot name="heading">Setup checklist</x-slot>
        <x-slot name="description">
            Follow these steps on any domain or host. Nothing here is tied to a specific seller server.
        </x-slot>

        <div class="mb-6">
            <div class="flex items-center justify-between gap-3 text-sm text-gray-800 dark:text-gray-200 mb-2">
                <span class="font-medium">{{ $progress['done'] }} of {{ $progress['total'] }} complete</span>
                <span class="tabular-nums text-gray-600 dark:text-gray-400">{{ $progress['percent'] }}%</span>
            </div>
            <div class="h-2.5 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                <div class="h-full rounded-full bg-primary-600 transition-all" style="width: {{ $progress['percent'] }}%"></div>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($this->steps() as $step)
                <div class="rounded-xl border p-4 sm:p-5
                    {{ $step['done']
                        ? 'border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/80 dark:bg-emerald-950/30'
                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900' }}">
                    <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-bold
                            {{ $step['done']
                                ? 'bg-emerald-600 text-white'
                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200' }}">
                            @if($step['done'])
                                ✓
                            @else
                                {{ $loop->iteration }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-base font-semibold text-gray-950 dark:text-white">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $step['body'] }}</p>
                            @if(!empty($step['href']) && !empty($step['cta']))
                                <a href="{{ $step['href'] }}"
                                    class="mt-3 inline-flex text-sm font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                    {{ $step['cta'] }} →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Quick links</x-slot>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
            <a href="{{ \App\Filament\Pages\SystemTools::getUrl() }}" class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-primary-400 transition text-gray-900 dark:text-gray-100">
                <span class="font-semibold">System tools</span>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Migrations, metrics sync, CSV import</p>
            </a>
            <a href="{{ \App\Filament\Pages\LinkHealthPage::getUrl() }}" class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-primary-400 transition text-gray-900 dark:text-gray-100">
                <span class="font-semibold">Link health</span>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Broken repo or website URLs</p>
            </a>
            <a href="{{ url('/') }}" target="_blank" class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-primary-400 transition text-gray-900 dark:text-gray-100">
                <span class="font-semibold">View public site</span>
                <p class="mt-1 text-gray-600 dark:text-gray-400">Opens in a new tab</p>
            </a>
        </div>
    </x-filament::section>
</x-filament-panels::page>
