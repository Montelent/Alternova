<div class="mx-auto max-w-4xl px-4 py-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Your account</h1>
            <p class="mt-1 text-slate-500">{{ $user->email }}</p>
        </div>
        <livewire:auth.logout />
    </div>

    <div class="grid gap-8 lg:grid-cols-2 mb-12">
        <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Profile</h2>
            @if($profileMessage)
                <p class="mb-3 text-sm text-emerald-600">{{ $profileMessage }}</p>
            @endif
            <form wire:submit="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input type="text" wire:model="name"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" wire:model="email"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold px-4 py-2 text-sm transition">
                    Save profile
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
            <h2 class="text-lg font-semibold mb-4">Change password</h2>
            @if($passwordMessage)
                <p class="mb-3 text-sm text-emerald-600">{{ $passwordMessage }}</p>
            @endif
            <form wire:submit="updatePassword" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Current password</label>
                    <input type="password" wire:model="current_password" autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    @error('current_password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">New password</label>
                    <input type="password" wire:model="password" autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm new password</label>
                    <input type="password" wire:model="password_confirmation" autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 dark:bg-slate-100 dark:text-slate-900 text-white font-semibold px-4 py-2 text-sm transition">
                    Update password
                </button>
            </form>
        </section>
    </div>

    <section class="mb-12">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Favorites</h2>
            <a href="{{ route('favorites') }}" class="text-sm text-brand-600 hover:underline">Full list</a>
        </div>
        @if($favorites->isEmpty())
            <p class="text-sm text-slate-500">No favorites yet. Star alternatives while browsing.</p>
        @else
            <ul class="space-y-3">
                @foreach($favorites->take(12) as $alt)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                        <a href="{{ route('alternatives.show', $alt) }}" class="font-medium hover:text-brand-600">{{ $alt->name }}</a>
                        <button type="button" wire:click="removeFavorite({{ $alt->id }})" class="text-xs text-slate-400 hover:text-rose-500">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section>
        <h2 class="text-xl font-semibold mb-4">Saved domains</h2>
        @if($domains->isEmpty())
            <p class="text-sm text-slate-500">Domains you save from the combinator appear here.</p>
        @else
            <ul class="space-y-3">
                @foreach($domains as $row)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                        <span class="font-mono text-sm">{{ $row->domain }}</span>
                        <button type="button" wire:click="removeDomain({{ $row->id }})" class="text-xs text-slate-400 hover:text-rose-500">Remove</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
