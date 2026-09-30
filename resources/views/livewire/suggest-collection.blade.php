<div class="mx-auto max-w-xl px-4 py-12">
    <div class="text-center mb-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-300 mb-2">Community</p>
        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Suggest a collection</h1>
        <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm">
            Propose a themed list (e.g. “Best Notion alternatives”). Editors review and may publish it.
        </p>
    </div>

    @if($submitted)
        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 p-8 text-center">
            <p class="font-semibold text-emerald-800 dark:text-emerald-200">Thanks — your collection idea was submitted.</p>
            @if($trackingUrl)
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-400">Track status:</p>
                <a href="{{ $trackingUrl }}" class="mt-1 inline-block text-sm font-medium text-brand-600 dark:text-brand-300 break-all hover:underline">{{ $trackingUrl }}</a>
            @endif
            <a href="{{ route('collections.index') }}" class="mt-6 inline-block text-sm font-semibold text-brand-600 dark:text-brand-300 hover:underline">Browse collections →</a>
        </div>
    @else
        <form wire:submit="submit" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4 shadow-sm">
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Your name</label>
                    <input type="text" wire:model="submitter_name" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" wire:model="submitter_email" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm">
                    @error('submitter_email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Collection title *</label>
                <input type="text" wire:model="title" placeholder="Best self-hosted note apps 2026"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm">
                @error('title') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Short description</label>
                <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Intro / editorial notes</label>
                <textarea wire:model="intro" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Alternative slugs * <span class="font-normal text-slate-400">(space or comma separated)</span></label>
                <textarea wire:model="alternative_slugs_text" rows="3" placeholder="outline appflowy penpot"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm font-mono"></textarea>
                @error('alternative_slugs_text') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                @if($examples->isNotEmpty())
                    <p class="mt-1 text-xs text-slate-400">Examples: {{ $examples->take(6)->implode(', ') }}</p>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Notes for editors</label>
                <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm"></textarea>
            </div>

            {{-- honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold py-2.5 text-sm transition">
                Submit for review
            </button>
        </form>
    @endif
</div>
