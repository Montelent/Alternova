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

    <section class="mb-12 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
        <h2 class="text-lg font-semibold mb-1">API keys</h2>
        <p class="text-sm text-slate-500 mb-4">
            Authenticated requests get <strong>600 req/min</strong> (anonymous: 60).
            Send <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">Authorization: Bearer YOUR_KEY</code>
            or <code class="text-xs bg-slate-100 dark:bg-slate-800 px-1 rounded">X-Api-Key</code>.
        </p>

        @if($apiKeyMessage)
            <p class="mb-3 text-sm text-emerald-600">{{ $apiKeyMessage }}</p>
        @endif

        @if($newPlainKey)
            <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-4">
                <p class="text-xs font-semibold text-amber-800 dark:text-amber-200 mb-2">Copy this key now — it will not be shown again</p>
                <code class="block break-all text-sm font-mono text-slate-900 dark:text-white">{{ $newPlainKey }}</code>
            </div>
        @endif

        <form wire:submit="createApiKey" class="flex flex-col sm:flex-row gap-3 mb-6">
            <input type="text" wire:model="apiKeyName" placeholder="Key label"
                class="flex-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
            <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold px-4 py-2 text-sm transition shrink-0">
                Create key
            </button>
        </form>
        @error('apiKeyName') <p class="mb-3 text-xs text-rose-600">{{ $message }}</p> @enderror

        @if($apiKeys->isEmpty())
            <p class="text-sm text-slate-500">No keys yet.</p>
        @else
            <ul class="space-y-3">
                @foreach($apiKeys as $key)
                    <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                        <div>
                            <p class="font-medium text-sm">{{ $key->name }}</p>
                            <p class="text-xs text-slate-500 font-mono">{{ $key->key_prefix }}… · {{ $key->request_count }} requests
                                @if($key->last_used_at) · last {{ $key->last_used_at->diffForHumans() }} @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($key->revoked_at)
                                <span class="text-xs text-rose-500 font-medium">Revoked</span>
                            @else
                                <span class="text-xs text-emerald-600 font-medium">Active</span>
                                <button type="button" wire:click="revokeApiKey({{ $key->id }})"
                                    wire:confirm="Revoke this API key? Apps using it will stop working."
                                    class="text-xs text-slate-400 hover:text-rose-500">Revoke</button>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mb-12">
        <h2 class="text-xl font-semibold mb-1">Watchlist</h2>
        <p class="text-sm text-slate-500 mb-4">Email alerts when health drops by 5+ points after a metrics sync.</p>
        @if($watched->isEmpty())
            <p class="text-sm text-slate-500">No watches yet. Open an alternative and click <strong>Watch</strong>.</p>
        @else
            <ul class="space-y-3">
                @foreach($watched as $alt)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                        <div class="min-w-0">
                            <a href="{{ route('alternatives.show', $alt) }}" class="font-medium hover:text-brand-600">{{ $alt->name }}</a>
                            <p class="text-xs text-slate-500">Health {{ number_format($alt->overall_health_score, 1) }}</p>
                        </div>
                        <button type="button" wire:click="removeWatch({{ $alt->id }})" class="text-xs text-slate-400 hover:text-rose-500 shrink-0">Unwatch</button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

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
