<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" wire:key="vote-{{ $alternative->id }}">
    <p class="text-sm font-semibold text-slate-900">Would you use this?</p>
    <p class="mt-1 text-xs text-slate-500">Community signal — one vote per visitor.</p>
    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="button"
            wire:click="vote"
            @disabled($hasVoted)
            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition
                {{ $hasVoted
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-default'
                    : 'bg-brand-600 text-white hover:bg-brand-700 shadow-sm shadow-brand-600/20' }}">
            <span>{{ $hasVoted ? 'Voted' : 'I would use this' }}</span>
        </button>
        <span class="text-sm text-slate-600">
            <strong class="text-slate-900">{{ number_format($votesCount) }}</strong> vote{{ $votesCount === 1 ? '' : 's' }}
        </span>
    </div>
    @if($voteMessage)
        <p class="mt-3 text-xs text-slate-500">{{ $voteMessage }}</p>
    @endif
</div>
