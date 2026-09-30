<div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm p-6 sm:p-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Pros & cons</h2>
            <p class="text-sm text-slate-500 mt-1">Community insights — upvote what resonates.</p>
        </div>
        @if($ready)
            <button type="button" wire:click="toggleForm"
                class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold px-4 py-2 transition">
                {{ $showForm ? 'Cancel' : 'Add a point' }}
            </button>
        @endif
    </div>

    @if($message)
        <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">{{ $message }}</p>
    @endif

    @if($showForm)
        <form wire:submit="submit" class="mb-8 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950/50 p-4 space-y-3">
            <div class="flex gap-4 text-sm">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" wire:model="type" value="pro" class="text-brand-600">
                    <span class="font-medium text-emerald-700 dark:text-emerald-300">Pro</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="radio" wire:model="type" value="con" class="text-brand-600">
                    <span class="font-medium text-rose-700 dark:text-rose-300">Con</span>
                </label>
            </div>
            <div>
                <textarea wire:model="body" rows="2" maxlength="280" placeholder="One clear point (max 280 chars)…"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                @error('body') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
            @guest
                <div>
                    <input type="text" wire:model="authorName" placeholder="Display name (optional)"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-sm">
                </div>
            @endguest
            <button type="submit" class="rounded-lg bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white text-sm font-semibold px-4 py-2">
                Submit
            </button>
            <p class="text-xs text-slate-400">New points may need moderation before they appear.</p>
        </form>
    @endif

    @if(! $ready)
        <p class="text-sm text-slate-500">Pros & cons will appear after migrations run.</p>
    @else
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 mb-3">Pros</h3>
                @forelse($pros as $item)
                    <div class="flex gap-3 items-start mb-3 rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50/50 dark:bg-emerald-950/20 p-3">
                        <button type="button" wire:click="upvote({{ $item->id }})"
                            class="shrink-0 rounded-lg border border-emerald-200 dark:border-emerald-800 px-2 py-1 text-xs font-bold text-emerald-800 dark:text-emerald-200 {{ in_array($item->id, $votedIds) ? 'ring-1 ring-emerald-400' : 'hover:bg-emerald-100 dark:hover:bg-emerald-900/40' }}">
                            ▲ {{ $item->votes_count }}
                        </button>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-800 dark:text-slate-100">{{ $item->body }}</p>
                            @if($item->author_name)
                                <p class="text-[11px] text-slate-400 mt-1">— {{ $item->author_name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No pros yet. Be the first.</p>
                @endforelse
            </div>
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-rose-600 dark:text-rose-400 mb-3">Cons</h3>
                @forelse($cons as $item)
                    <div class="flex gap-3 items-start mb-3 rounded-xl border border-rose-100 dark:border-rose-900/40 bg-rose-50/50 dark:bg-rose-950/20 p-3">
                        <button type="button" wire:click="upvote({{ $item->id }})"
                            class="shrink-0 rounded-lg border border-rose-200 dark:border-rose-800 px-2 py-1 text-xs font-bold text-rose-800 dark:text-rose-200 {{ in_array($item->id, $votedIds) ? 'ring-1 ring-rose-400' : 'hover:bg-rose-100 dark:hover:bg-rose-900/40' }}">
                            ▲ {{ $item->votes_count }}
                        </button>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-800 dark:text-slate-100">{{ $item->body }}</p>
                            @if($item->author_name)
                                <p class="text-[11px] text-slate-400 mt-1">— {{ $item->author_name }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No cons yet. Be the first.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
