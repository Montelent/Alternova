<div class="mt-4">
    @if(!$open && !$sent)
        <button type="button" wire:click="openForm"
            class="text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 underline-offset-2 hover:underline">
            Report an issue with this page
        </button>
    @endif

    @if($sent)
        <p class="text-sm text-emerald-600 dark:text-emerald-400">{{ $statusMessage }}</p>
    @elseif($open)
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm max-w-lg">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-900 dark:text-white text-sm">Report an issue</h3>
                <button type="button" wire:click="closeForm" class="text-slate-400 hover:text-slate-600 text-sm">Close</button>
            </div>

            @if($statusMessage)
                <p class="mb-2 text-sm text-red-600">{{ $statusMessage }}</p>
            @endif

            <form wire:submit="submit" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Type</label>
                    <select wire:model="type" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm">
                        <option value="broken_link">Broken link</option>
                        <option value="wrong_info">Wrong information</option>
                        <option value="spam">Spam / low quality</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Details</label>
                    <textarea wire:model="message" rows="4" required
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm"
                        placeholder="What should we fix?"></textarea>
                    @error('message') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Email (optional)</label>
                    <input type="email" wire:model="email"
                        class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm"
                        placeholder="If you want a reply">
                </div>
                <button type="submit" wire:loading.attr="disabled"
                    class="rounded-xl bg-brand-600 text-white text-sm font-semibold px-4 py-2 hover:bg-brand-700 disabled:opacity-50">
                    Submit report
                </button>
            </form>
        </div>
    @endif
</div>
