<?php

namespace App\Livewire;

use App\Models\AlternativeComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CommentSection extends Component
{
    public int $alternativeId;

    public string $author_name = '';

    public string $author_email = '';

    public string $body = '';

    public string $message = '';

    public bool $ready = false;

    /** When set, the next submit is a reply to this comment id. */
    public ?int $replyToId = null;

    public string $replyToName = '';

    public function mount(int $alternativeId): void
    {
        $this->alternativeId = $alternativeId;

        try {
            $this->ready = Schema::hasTable('alternative_comments');
        } catch (\Throwable) {
            $this->ready = false;
        }

        if (Auth::check()) {
            $this->author_name = (string) Auth::user()->name;
            $this->author_email = (string) Auth::user()->email;
        }
    }

    public function startReply(int $commentId, string $authorName): void
    {
        $this->replyToId = $commentId;
        $this->replyToName = $authorName;
        $this->message = '';
        $this->dispatch('focus-comment-form');
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
        $this->replyToName = '';
    }

    public function submit(): void
    {
        if (! $this->ready) {
            $this->message = 'Comments are not available yet. Run migrations.';

            return;
        }

        $key = 'comment:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            $this->message = 'Too many comments from this network. Try later.';

            return;
        }

        $rules = [
            'body' => 'required|string|min:3|max:2000',
            'author_name' => 'required|string|max:80',
        ];

        if (! Auth::check()) {
            $rules['author_email'] = 'nullable|email|max:190';
        }

        $data = $this->validate($rules);

        if (preg_match('/https?:\/\//i', $data['body']) && substr_count(strtolower($data['body']), 'http') > 2) {
            $this->message = 'Too many links — comment blocked.';

            return;
        }

        $parentId = null;
        if ($this->replyToId) {
            $parent = AlternativeComment::query()
                ->where('id', $this->replyToId)
                ->where('open_source_alternative_id', $this->alternativeId)
                ->first();

            if (! $parent) {
                $this->message = 'Parent comment not found.';
                $this->cancelReply();

                return;
            }

            // One level of nesting: reply to a reply attaches to the root parent
            $parentId = $parent->parent_id ?: $parent->id;
        }

        RateLimiter::hit($key, 3600);

        $approved = Auth::check();

        AlternativeComment::create([
            'open_source_alternative_id' => $this->alternativeId,
            'user_id' => Auth::id(),
            'parent_id' => $parentId,
            'author_name' => $data['author_name'],
            'author_email' => Auth::check() ? Auth::user()->email : ($data['author_email'] ?? null),
            'body' => strip_tags($data['body']),
            'is_approved' => $approved,
            'is_hidden' => false,
            'ip_address' => request()->ip(),
        ]);

        $this->body = '';
        $wasReply = (bool) $this->replyToId;
        $this->cancelReply();

        $this->message = $approved
            ? ($wasReply ? 'Reply published.' : 'Comment published.')
            : 'Thanks — your '.($wasReply ? 'reply' : 'comment').' is awaiting moderation.';
    }

    public function render()
    {
        $comments = collect();

        if ($this->ready) {
            try {
                $comments = AlternativeComment::query()
                    ->where('open_source_alternative_id', $this->alternativeId)
                    ->visible()
                    ->whereNull('parent_id')
                    ->with(['replies' => function ($q) {
                        $q->visible()->orderBy('created_at');
                    }])
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get();
            } catch (\Throwable) {
            }
        }

        $totalVisible = $comments->count() + $comments->sum(fn ($c) => $c->replies->count());

        return view('livewire.comment-section', [
            'comments' => $comments,
            'totalVisible' => $totalVisible,
            'isLoggedIn' => Auth::check(),
        ]);
    }
}
