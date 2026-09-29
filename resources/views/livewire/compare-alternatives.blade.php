<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
    @if($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    <div class="text-center mb-10">
        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 mb-2">Compare</p>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Open-source alternatives</h1>
        <p class="mt-3 text-slate-600 dark:text-slate-400 max-w-xl mx-auto">Pick two projects and compare license, health, stars, and self-host difficulty. Share a permanent link with anyone.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-sm mb-8">
        <div class="grid sm:grid-cols-[1fr_auto_1fr] gap-4 items-end">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Option A</label>
                <select wire:model.live="leftSlug" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Select alternative…</option>
                    @foreach($options as $opt)
                        <option value="{{ $opt->slug }}" @disabled($opt->slug === $rightSlug)>{{ $opt->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-center pb-1">
                <button type="button" wire:click="swap" class="rounded-full border border-slate-200 dark:border-slate-700 p-2 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800" title="Swap">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </button>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Option B</label>
                <select wire:model.live="rightSlug" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-950 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Select alternative…</option>
                    @foreach($options as $opt)
                        <option value="{{ $opt->slug }}" @disabled($opt->slug === $leftSlug)>{{ $opt->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($shareUrl)
            <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-800"
                 x-data="{ copied: false, url: @js($shareUrl) }">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Share this comparison</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" readonly :value="url"
                        class="flex-1 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 px-3 py-2 text-xs sm:text-sm font-mono text-slate-700 dark:text-slate-200"
                        onclick="this.select()">
                    <div class="flex gap-2 shrink-0">
                        <button type="button"
                            @click="navigator.clipboard.writeText(url); copied = true; $wire.markCopied(); setTimeout(() => copied = false, 2000)"
                            class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold px-4 py-2 text-sm transition">
                            <span x-show="!copied">Copy link</span>
                            <span x-show="copied" x-cloak>Copied!</span>
                        </button>
                        <button type="button"
                            @click="if (navigator.share) { navigator.share({ title: document.title, url: url }).catch(() => {}) } else { navigator.clipboard.writeText(url); copied = true; }"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                            Share
                        </button>
                    </div>
                </div>
                @if($shareCopied)
                    <p class="mt-2 text-xs text-emerald-600">{{ $shareCopied }}</p>
                @endif
                <p class="mt-2 text-xs text-slate-400">Anyone with this URL sees the same side-by-side comparison. Also works as <code class="text-[11px]">?tools=slug-a,slug-b</code>.</p>
            </div>
        @endif
    </div>

    @if($left && $right)
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
            @foreach([$left, $right] as $side)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $side->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">vs {{ $side->proprietaryTool?->name ?? 'proprietary tools' }}</p>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-300 line-clamp-3">{{ $side->description }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('alternatives.show', $side) }}" class="text-sm font-semibold text-brand-600 hover:underline">Full page →</a>
                        @if($side->repo_url)
                            <a href="{{ $side->repo_url }}" target="_blank" rel="noopener" class="text-sm text-slate-500 hover:underline">GitHub</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/50">
                        <th class="py-3 px-4 text-left font-semibold text-slate-500 w-1/3">Metric</th>
                        <th class="py-3 px-4 text-center font-semibold text-slate-800 dark:text-slate-100">{{ $left->name }}</th>
                        <th class="py-3 px-4 text-center font-semibold text-slate-800 dark:text-slate-100">{{ $right->name }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                    @foreach($rows as $row)
                        <tr>
                            <td class="py-3 px-4 font-medium text-slate-600 dark:text-slate-400">{{ $row['label'] }}</td>
                            <td class="py-3 px-4 text-center {{ $row['winner'] === 'left' ? 'bg-emerald-50 dark:bg-emerald-900/20 font-semibold text-emerald-800 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $row['left'] }}
                                @if($row['winner'] === 'left') <span class="text-xs">✓</span> @endif
                            </td>
                            <td class="py-3 px-4 text-center {{ $row['winner'] === 'right' ? 'bg-emerald-50 dark:bg-emerald-900/20 font-semibold text-emerald-800 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-200' }}">
                                {{ $row['right'] }}
                                @if($row['winner'] === 'right') <span class="text-xs">✓</span> @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="mt-4 text-xs text-slate-400 text-center">
            Higher health/stars/forks win those rows. Lower difficulty and open issues win those rows. Ties are unmarked.
        </p>
    @else
        <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 p-10 text-center text-slate-500">
            Select two alternatives above to see a side-by-side comparison.
        </div>
    @endif

    <div class="mt-10 text-center">
        <a href="{{ route('finder') }}" class="text-sm font-semibold text-brand-600 hover:underline">← Back to all alternatives</a>
    </div>
</div>
