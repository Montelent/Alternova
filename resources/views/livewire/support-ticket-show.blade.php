<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Support ticket</p>
        <h1 class="mt-1 text-2xl sm:text-3xl font-bold text-slate-900 dark:text-white">
            {{ $ticket->public_id ?: '#'.$ticket->id }}
        </h1>
        <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $ticket->subject ?: 'Support request' }}</p>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-slate-700 dark:text-slate-300">
                Status: {{ str_replace('_', ' ', $ticket->ticket_status ?? $ticket->status) }}
            </span>
            <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-slate-700 dark:text-slate-300">
                Opened {{ $ticket->created_at?->diffForHumans() }}
            </span>
        </div>
    </div>

    @if($errorMessage)
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 text-rose-800 dark:text-rose-200 text-sm px-4 py-3">{{ $errorMessage }}</div>
    @endif
    @if($successMessage)
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-200 text-sm px-4 py-3">{{ $successMessage }}</div>
    @endif

    <div class="space-y-4 mb-10">
        {{-- Original message --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center justify-between gap-2 mb-2">
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $ticket->name }}</p>
                <p class="text-xs text-slate-500">{{ $ticket->created_at?->format('M j, Y H:i') }}</p>
            </div>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $ticket->message }}</p>
        </div>

        @foreach($ticket->publicReplies as $reply)
            <div class="rounded-2xl border p-5 {{ $reply->isStaff() ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/60 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900' }}">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                        {{ $reply->author_name ?: ($reply->isStaff() ? 'Support' : 'You') }}
                        @if($reply->isStaff())
                            <span class="ml-1 text-[10px] uppercase tracking-wide text-indigo-600 dark:text-indigo-300">Staff</span>
                        @endif
                    </p>
                    <p class="text-xs text-slate-500">{{ $reply->created_at?->format('M j, Y H:i') }}</p>
                </div>
                <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $reply->body }}</p>
            </div>
        @endforeach
    </div>

    @if($ticket->isOpen())
        @if($canReply)
            <form wire:submit="sendReply" class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-3">
                <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Your reply</h2>
                <textarea wire:model="replyBody" rows="4" class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2.5 text-sm" placeholder="Write your reply…"></textarea>
                @error('replyBody') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                <button type="submit" class="rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2.5">
                    Send reply
                </button>
                <p class="text-xs text-slate-500">Support is notified by email. You also get an in-app notification.</p>
            </form>
        @elseif(!$isLoggedIn)
            <div class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30 p-5 text-sm text-amber-900 dark:text-amber-100">
                <p class="font-semibold">Sign in to reply</p>
                <p class="mt-1">Use the same email as this ticket (<span class="font-mono">{{ $ticket->email }}</span>) to post a reply.</p>
                <a href="{{ route('login') }}" class="inline-flex mt-3 rounded-xl bg-amber-700 text-white font-semibold px-4 py-2 text-sm">Sign in</a>
            </div>
        @else
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 text-sm text-slate-600 dark:text-slate-400">
                You are signed in, but this account email does not match the ticket. Sign in as <span class="font-mono">{{ $ticket->email }}</span> to reply.
            </div>
        @endif
    @else
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 p-5 text-sm text-slate-600 dark:text-slate-400">
            This ticket is closed. <a href="{{ route('contact') }}" class="text-indigo-600 font-medium hover:underline">Open a new ticket</a> if you need more help.
        </div>
    @endif
</div>
