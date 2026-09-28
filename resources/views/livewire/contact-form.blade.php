<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Contact</h1>
    <p class="mt-3 text-slate-600 leading-relaxed">
        Corrections, listing requests, or general feedback — send a message. We read every note.
    </p>

    @if($sent)
        <div class="mt-10 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-900">
            <p class="font-semibold text-lg">Message sent</p>
            <p class="mt-2 text-sm text-emerald-800">Thanks. We’ll review it as soon as we can.</p>
            <a href="{{ route('home') }}" class="inline-block mt-4 text-sm font-semibold text-emerald-700 hover:underline">Back to home →</a>
        </div>
    @else
        <form wire:submit="submit" class="mt-10 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                    <input type="text" wire:model="name" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                    <input type="email" wire:model="email" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                <input type="text" wire:model="subject" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500">
                @error('subject') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Message *</label>
                <textarea wire:model="message" rows="6" class="w-full rounded-xl border-slate-300 text-sm focus:ring-brand-500 focus:border-brand-500"></textarea>
                @error('message') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled"
                class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60 transition">
                <span wire:loading.remove wire:target="submit">Send message</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
        </form>
    @endif

    <p class="mt-8 text-sm text-slate-500">
        <a href="{{ route('privacy') }}" class="underline hover:text-slate-800">Privacy</a>
        ·
        <a href="{{ route('terms') }}" class="underline hover:text-slate-800">Terms</a>
    </p>
</div>
