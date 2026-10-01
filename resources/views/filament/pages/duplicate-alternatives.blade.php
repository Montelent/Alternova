<x-filament-panels::page>
    <div class="space-y-6 text-gray-950 dark:text-gray-100">
        <x-filament::section>
            <x-slot name="heading">How this works</x-slot>
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                This page lists <strong>possible</strong> duplicates so you can merge or unpublish by hand.
                Same-repo matches are the strongest signal. Similar names are only a hint (e.g. “Outline” vs “Outline Wiki”).
            </p>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Same repository URL</x-slot>
            <x-slot name="description">Two or more records point at the same GitHub (or other) repo after normalization.</x-slot>

            @php $groups = $this->sameRepoGroups(); @endphp
            @if($groups->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No same-repo groups found.</p>
            @else
                <div class="space-y-4">
                    @foreach($groups as $repo => $items)
                        <div class="rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                            <p class="font-mono text-xs text-amber-900 dark:text-amber-200 break-all mb-3">{{ $repo }}</p>
                            <ul class="space-y-2">
                                @foreach($items as $alt)
                                    <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $alt->name }}</span>
                                            <span class="text-gray-500">· {{ $alt->is_published ? 'Published' : 'Draft' }}</span>
                                            @if($alt->proprietaryTool)
                                                <span class="text-gray-500">· vs {{ $alt->proprietaryTool->name }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ $this->editUrl($alt->id) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline shrink-0">Edit</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Very similar names (≥ 85%)</x-slot>
            <x-slot name="description">Review carefully — not every similar name is a true duplicate.</x-slot>

            @php $pairs = $this->similarPairs(); @endphp
            @if($pairs->isEmpty())
                <p class="text-sm text-gray-600 dark:text-gray-400">No high-similarity name pairs found.</p>
            @else
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($pairs as $pair)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-sm">
                            <div class="min-w-0">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $pair['a']->name }}</span>
                                <span class="text-gray-400 mx-1">↔</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $pair['b']->name }}</span>
                                <span class="ml-2 text-xs font-bold text-amber-700 dark:text-amber-300">{{ number_format($pair['score'], 0) }}% match</span>
                            </div>
                            <div class="flex gap-3 shrink-0">
                                <a href="{{ $this->editUrl($pair['a']->id) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Edit A</a>
                                <a href="{{ $this->editUrl($pair['b']->id) }}" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Edit B</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
