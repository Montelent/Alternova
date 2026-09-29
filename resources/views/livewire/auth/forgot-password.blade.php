<div class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Forgot password</h1>
        <p class="mt-2 text-sm text-slate-500">Enter your email and we will send a reset link (requires Mail settings / SMTP).</p>

        @if($status)
            <div class="mt-6 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-200 text-sm px-4 py-3">
                {{ $status }}
            </div>
        @endif

        <form wire:submit="sendResetLink" class="mt-8 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold py-2.5 text-sm transition">
                Send reset link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="{{ route('login') }}" class="text-brand-600 hover:underline font-medium">Back to sign in</a>
        </p>
    </div>
</div>
