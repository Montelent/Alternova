<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden"
     x-data
     @focus-comment-form.window="$nextTick(() => $refs.commentBody?.focus())">
    <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/50 px-6 py-4">
        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
            Discussion
            @if($totalVisible > 0)
                <span class="text-sm font-normal text-slate-500">({{ $totalVisible }})</span>
            @endif
        </h2>
        <p class="text-sm text-slate-500 mt-1">Share experience self-hosting or comparing tools. Be respectful.</p>
    </div>

    <div class="p-6 sm:p-8">
        @if(! $ready)
            <p class="text-sm text-slate-500">Comments will appear after migrations are run.</p>
        @else
            @if($message)
                <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-200 text-sm px-4 py-3">
                    {{ $message }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-4 mb-10" id="comment-form">
                @if($replyToId)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-brand-200 bg-brand-50/60 dark:bg-brand-950/30 dark:border-brand-800 px-3 py-2 text-sm">
                        <span class="text-slate-700 dark:text-slate-200">
                            Replying to <strong>{{ $replyToName }}</strong>
                        </span>
                        <button type="button" wire:click="cancelReply" class="text-xs font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">
                            Cancel
                        </button>
                    </div>
                @endif

                @if(! $isLoggedIn)
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Name</label>
                            <input type="text" wire:model="author_name"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                            @error('author_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email <span class="text-slate-400">(optional, not published)</span></label>
                            <input type="email" wire:model="author_email"
                                class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                            @error('author_email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Guest comments are moderated before they appear. <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Sign in</a> to publish instantly.</p>
                @else
                    <p class="text-sm text-slate-500">Commenting as <strong>{{ $author_name }}</strong></p>
                @endif

                <div>
                    <label class="block text-sm font-medium mb-1">{{ $replyToId ? 'Your reply' : 'Comment' }}</label>
                    <textarea wire:model="body" rows="4" maxlength="2000" x-ref="commentBody"
                        placeholder="{{ $replyToId ? 'Write a reply…' : 'What worked for you? Any gotchas when self-hosting?' }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                    @error('body') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold px-5 py-2.5 text-sm transition">
                    {{ $replyToId ? 'Post reply' : 'Post comment' }}
                </button>
            </form>

            @if($comments->isEmpty())
                <p class="text-sm text-slate-500">No comments yet. Be the first to share.</p>
            @else
                <ul class="space-y-6">
                    @foreach($comments as $comment)
                        <li class="border-b border-slate-100 dark:border-slate-800 pb-6 last:border-0 last:pb-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-700 text-xs font-bold">
                                    {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $comment->author_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $comment->created_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $comment->body }}</p>
                            <button type="button"
                                wire:click="startReply({{ $comment->id }}, {{ json_encode($comment->author_name) }})"
                                class="mt-2 text-xs font-semibold text-brand-600 hover:underline">
                                Reply
                            </button>

                            @if($comment->replies->isNotEmpty())
                                <ul class="mt-4 space-y-4 border-l-2 border-slate-100 dark:border-slate-800 pl-4 sm:pl-5">
                                    @foreach($comment->replies as $reply)
                                        <li>
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold">
                                                    {{ strtoupper(substr($reply->author_name, 0, 1)) }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $reply->author_name }}</p>
                                                    <p class="text-xs text-slate-400">{{ $reply->created_at?->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $reply->body }}</p>
                                            <button type="button"
                                                wire:click="startReply({{ $reply->id }}, {{ json_encode($reply->author_name) }})"
                                                class="mt-1.5 text-xs font-semibold text-brand-600 hover:underline">
                                                Reply
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</div>
