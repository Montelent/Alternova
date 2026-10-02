<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6 mt-6">
    <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Conversation</h3>
    <div class="space-y-4">
        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 mb-1">Original · {{ $ticket->created_at }}</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $ticket->message }}</p>
        </div>
        @forelse($replies as $reply)
            <div class="rounded-lg border p-4 {{ $reply->is_internal ? 'border-amber-300 bg-amber-50 dark:bg-amber-950/20' : ($reply->isStaff() ? 'border-indigo-200 bg-indigo-50/50 dark:bg-indigo-950/20' : 'border-gray-200 dark:border-white/10') }}">
                <p class="text-xs text-gray-500 mb-1">
                    {{ $reply->author_name }} · {{ $reply->author_type }}
                    @if($reply->is_internal) · internal @endif
                    · {{ $reply->created_at }}
                </p>
                <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ $reply->body }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-500">No replies yet. Use “Reply to user” above.</p>
        @endforelse
    </div>
</div>
