<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Suggest an alternative</h1>
    <p class="mt-3 text-slate-600 dark:text-slate-400 leading-relaxed">
        Know a solid open-source project that should be listed? Submit it for review.
        We check licenses, activity, and quality before publishing.
    </p>

    @if($submitted)
        <div class="mt-10 rounded-2xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/30 p-6 text-emerald-900 dark:text-emerald-100">
            <p class="font-semibold text-lg">Thanks — we received your suggestion.</p>
            <p class="mt-2 text-sm text-emerald-800 dark:text-emerald-200">An editor will review it. Published listings appear on the Alternatives page.</p>
            @if($trackingUrl)
                <div class="mt-4 rounded-xl bg-white/70 dark:bg-slate-900/50 border border-emerald-200 dark:border-emerald-800 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300 mb-1">Track this submission</p>
                    <a href="{{ $trackingUrl }}" class="text-sm break-all font-medium text-brand-700 dark:text-brand-300 hover:underline">{{ $trackingUrl }}</a>
                    <p class="mt-1 text-xs text-emerald-700/80 dark:text-emerald-300/80">Bookmark this link to check status later.</p>
                </div>
            @endif
            <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold">
                @if($trackingUrl)
                    <a href="{{ $trackingUrl }}" class="text-emerald-700 dark:text-emerald-300 hover:underline">View status →</a>
                @endif
                <a href="{{ route('finder') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline">Browse alternatives →</a>
            </div>
        </div>
    @else
        <form wire:submit="submit" class="mt-10 space-y-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 shadow-sm">
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label>Website</label>
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Your name <span class="text-slate-400">(optional)</span></label>
                    <input type="text" wire:model="submitter_name" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('submitter_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Your email <span class="text-slate-400">(optional)</span></label>
                    <input type="email" wire:model="submitter_email" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('submitter_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Proprietary product it replaces *</label>
                <input type="text" wire:model="proprietary_name" placeholder="e.g. Notion" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('proprietary_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Open-source alternative name *</label>
                <input type="text" wire:model="alternative_name" placeholder="e.g. AppFlowy" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('alternative_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">GitHub repository URL *</label>
                <input type="url" wire:model="repo_url" placeholder="https://github.com/owner/repo" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('repo_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Project website <span class="text-slate-400">(optional)</span></label>
                <input type="url" wire:model="website_url" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('website_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">License <span class="text-slate-400">(optional)</span></label>
                <input type="text" wire:model="license_type" placeholder="MIT, Apache-2.0, AGPL-3.0…" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Short description <span class="text-slate-400">(optional)</span></label>
                <textarea wire:model="description" rows="4" class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-brand-500 focus:border-brand-500"></textarea>
                @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60 transition">
                <span wire:loading.remove wire:target="submit">Submit for review</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
        </form>
    @endif
</div>
