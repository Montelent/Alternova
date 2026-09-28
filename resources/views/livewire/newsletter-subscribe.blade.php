<div class="w-full max-w-md">
    @if($message)
        <p class="mb-2 text-sm {{ $success ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
            {{ $message }}
        </p>
    @endif
    <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-2">
        <input
            type="email"
            wire:model="email"
            required
            placeholder="you@example.com"
            class="flex-1 rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            autocomplete="email"
        >
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2 disabled:opacity-50"
        >
            <span wire:loading.remove wire:target="subscribe">Subscribe</span>
            <span wire:loading wire:target="subscribe">…</span>
        </button>
    </form>
    <p class="mt-2 text-xs text-slate-400">No spam. Unsubscribe anytime.</p>
</div>
