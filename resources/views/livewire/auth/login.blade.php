<div class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Sign in</h1>
        <p class="mt-2 text-sm text-slate-500">Sync favorites across devices with your free account.</p>

        <form wire:submit="login" class="mt-8 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" wire:model="password" autocomplete="current-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" wire:model="remember" class="rounded border-slate-300">
                Remember me
            </label>
            <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold py-2.5 text-sm transition">
                Sign in
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            No account?
            <a href="{{ route('register') }}" class="text-brand-600 hover:underline font-medium">Create one</a>
        </p>
    </div>
</div>
