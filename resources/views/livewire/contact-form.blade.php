<div class="max-w-2xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Contact / Support</h1>
    <p class="mt-3 text-slate-600 dark:text-slate-400 leading-relaxed">
        Corrections, listing requests, or general feedback. Your message becomes a <strong>support ticket</strong>
        you can follow — and reply to after signing in with the same email.
    </p>

    @if($sent)
        <div class="mt-10 rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 p-6 text-emerald-900 dark:text-emerald-100">
            <p class="font-semibold text-lg">Ticket created</p>
            <p class="mt-2 text-sm">Thanks. We’ll review it as soon as we can.</p>
            @if($ticketPublicId)
                <p class="mt-3 text-sm font-mono">Ticket ID: <strong>{{ $ticketPublicId }}</strong></p>
            @endif
            @if($ticketUrl)
                <a href="{{ $ticketUrl }}" class="inline-flex mt-4 rounded-xl bg-emerald-700 text-white text-sm font-semibold px-4 py-2.5 hover:bg-emerald-600">
                    View ticket & replies
                </a>
            @endif
            <p class="mt-4 text-xs text-emerald-800 dark:text-emerald-200/80">
                We also emailed a copy to your address (if mail is configured).
                To reply on this ticket later, <a href="{{ route('login') }}" class="underline font-semibold">sign in</a> with the same email.
            </p>
            <a href="{{ route('home') }}" class="inline-block mt-4 text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Back home</a>
        </div>
    @else
        <form wire:submit="submit" class="mt-10 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name</label>
                    <input type="text" wire:model="name" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900 px-3 py-2.5 text-sm" required>
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900 px-3 py-2.5 text-sm" required>
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Subject (optional)</label>
                <input type="text" wire:model="subject" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Message</label>
                <textarea wire:model="message" rows="6" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900 px-3 py-2.5 text-sm" required></textarea>
                @error('message') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            {{-- honeypot --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="w-full sm:w-auto rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 text-sm disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Open support ticket</span>
                <span wire:loading wire:target="submit">Sending…</span>
            </button>
            @guest
                <p class="text-xs text-slate-500">Already have an account?
                    <a href="{{ route('login') }}" class="text-indigo-600 font-medium hover:underline">Sign in</a>
                    so replies sync to your account and notifications.</p>
            @endguest
        </form>
    @endif
</div>
