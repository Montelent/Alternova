<div class="mx-auto max-w-4xl px-4 py-12">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Your account</h1>
            <p class="mt-1 text-slate-500">{{ $user->email }}</p>
        </div>
        <livewire:auth.logout />
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
            <p class="text-sm text-slate-500">Domains you save from the combinator will appear here after you migrate the table.</p>
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
