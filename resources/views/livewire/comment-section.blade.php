<div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/50 px-6 py-4">
        <h2 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white">
            Discussion
            @if($comments->isNotEmpty())
                <span class="text-sm font-normal text-slate-500">({{ $comments->count() }})</span>
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

            <form wire:submit="submit" class="space-y-4 mb-10">
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
                    <label class="block text-sm font-medium mb-1">Comment</label>
                    <textarea wire:model="body" rows="4" maxlength="2000"
                        placeholder="What worked for you? Any gotchas when self-hosting?"
                        class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
                    @error('body') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-semibold px-5 py-2.5 text-sm transition">
                    Post comment
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
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
</div>
