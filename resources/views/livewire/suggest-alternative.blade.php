<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Suggest an alternative</h1>
    <p class="mt-3 text-slate-600 leading-relaxed">
        Know a solid open-source project that should be listed? Submit it for review.
        We check licenses, activity, and quality before publishing.
    </p>

    @if($submitted)
        <div class="mt-10 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-900">
            <p class="font-semibold text-lg">Thanks — we received your suggestion.</p>
            <p class="mt-2 text-sm text-emerald-800">An editor will review it. Published listings appear on the Alternatives page.</p>
            <a href="{{ route('finder') }}" class="inline-block mt-4 text-sm font-semibold text-emerald-700 hover:underline">Browse alternatives →</a>
        </div>
    @else
        <form wire:submit="submit" class="mt-10 space-y-6 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            {{-- Honeypot --}}
            <div class="hidden" aria-hidden="true">
                <label>Website</label>
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Your name <span class="text-slate-400">(optional)</span></label>
                    <input type="text" wire:model="submitter_name" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('submitter_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Your email <span class="text-slate-400">(optional)</span></label>
                    <input type="email" wire:model="submitter_email" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('submitter_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proprietary product it replaces *</label>
                <input type="text" wire:model="proprietary_name" placeholder="e.g. Notion" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('proprietary_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Open-source alternative name *</label>
                <input type="text" wire:model="alternative_name" placeholder="e.g. AppFlowy" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('alternative_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">GitHub repository URL *</label>
                <input type="url" wire:model="repo_url" placeholder="https://github.com/owner/repo" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('repo_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Project website <span class="text-slate-400">(optional)</span></label>
                <input type="url" wire:model="website_url" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('website_url') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">License <span class="text-slate-400">(optional)</span></label>
                <input type="text" wire:model="license_type" placeholder="MIT, Apache-2.0, AGPL-3.0…" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Short description <span class="text-slate-400">(optional)</span></label>
                <textarea wire:model="description" rows="4" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500"></textarea>
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
