<div class="mx-auto max-w-2xl px-4 py-12">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Notifications</h1>
            <p class="mt-1 text-sm text-slate-500">
                @if($unread > 0)
                    {{ $unread }} unread
                @else
                    You're all caught up
                @endif
            </p>
        </div>
        @if($unread > 0)
            <button type="button" wire:click="markAllRead"
                class="text-sm font-semibold text-brand-600 dark:text-brand-300 hover:underline">
                Mark all read
            </button>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-12 text-center text-slate-500">
            No notifications yet. Watch alternatives or leave comments to get updates here.
        </div>
    @else
        <ul class="space-y-3">
            @foreach($notifications as $n)
                <li class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 {{ $n->read_at ? 'opacity-70' : 'ring-1 ring-brand-200 dark:ring-brand-800' }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400 mb-1">
                                {{ str_replace('_', ' ', $n->type) }}
                            </p>
                            @if($n->url)
                                <a href="{{ $n->url }}" wire:click="markRead({{ $n->id }})"
                                    class="font-semibold text-slate-900 dark:text-white hover:text-brand-600 dark:hover:text-brand-300">
                                    {{ $n->title }}
                                </a>
                            @else
                                <p class="font-semibold text-slate-900 dark:text-white">{{ $n->title }}</p>
                            @endif
                            @if($n->body)
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ $n->body }}</p>
                            @endif
                            <p class="mt-2 text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                        @if(! $n->read_at)
                            <button type="button" wire:click="markRead({{ $n->id }})"
                                class="shrink-0 text-xs text-brand-600 dark:text-brand-300 hover:underline">
                                Mark read
                            </button>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <p class="mt-8 text-center text-sm text-slate-500">
        <a href="{{ route('account') }}" class="text-brand-600 dark:text-brand-300 font-medium hover:underline">← Account</a>
    </p>
</div>
