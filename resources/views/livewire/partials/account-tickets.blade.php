<section class="mb-12 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Support tickets</h2>
            <p class="text-sm text-slate-500">Messages from Contact. Reply when signed in with the same email.</p>
        </div>
        <a href="{{ route('contact') }}" class="text-sm font-medium text-indigo-600 hover:underline">New ticket</a>
    </div>
    @if(($tickets ?? collect())->isEmpty())
        <p class="text-sm text-slate-500">No tickets yet.</p>
    @else
        <ul class="space-y-3">
            @foreach($tickets as $t)
                <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                    <div class="min-w-0">
                        <a href="{{ $t->publicUrl() }}" class="font-medium text-slate-900 dark:text-white hover:text-indigo-600">
                            {{ $t->public_id ?: '#'.$t->id }}
                        </a>
                        <p class="text-xs text-slate-500 truncate">{{ $t->subject ?: 'Support request' }} · {{ str_replace('_', ' ', $t->ticket_status ?? $t->status) }}</p>
                    </div>
                    <a href="{{ $t->publicUrl() }}" class="text-xs font-semibold text-indigo-600 hover:underline shrink-0">Open</a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
