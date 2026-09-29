<div class="mx-auto max-w-md px-4 py-16">
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create account</h1>
        <p class="mt-2 text-sm text-slate-500">Free. Keeps favorites and saved domains with you on any device.</p>

        <form wire:submit="register" class="mt-8 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" wire:model="name" autocomplete="name"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" wire:model="email" autocomplete="email"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" wire:model="password" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm password</label>
                <input type="password" wire:model="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <button type="submit" class="w-full rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold py-2.5 text-sm transition">
                Create account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>
</div>
