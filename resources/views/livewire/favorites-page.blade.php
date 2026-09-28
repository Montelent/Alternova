<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Favorites</h1>
            <p class="mt-2 text-slate-500 dark:text-slate-400 text-sm">Saved on this browser/session only — not linked to an account.</p>
        </div>
        @if($favorites->isNotEmpty())
            <button type="button" wire:click="clearAll" wire:confirm="Clear all favorites?"
                class="text-sm font-semibold text-red-600 hover:underline">Clear all</button>
        @endif
    </div>

    @if($favorites->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-12 text-center text-slate-500">
            <p>No favorites yet.</p>
            <a href="{{ route('finder') }}" class="mt-3 inline-block text-brand-600 font-semibold hover:underline">Browse alternatives</a>
        </div>
    @else
        <ul class="space-y-4">
            @foreach($favorites as $alt)
                <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 flex items-start justify-between gap-4">
                    <div>
                        <a href="{{ route('alternatives.show', $alt) }}" class="text-lg font-semibold text-slate-900 dark:text-white hover:text-brand-600">
                            {{ $alt->name }}
                        </a>
                        <p class="mt-1 text-sm text-slate-500">
                            vs {{ $alt->proprietaryTool?->name ?? 'proprietary tools' }}
                        </p>
                    </div>
                    <button type="button" wire:click="remove({{ $alt->id }})" class="text-sm text-slate-400 hover:text-red-500">Remove</button>
                </li>
            @endforeach
        </ul>
    @endif
</div>
