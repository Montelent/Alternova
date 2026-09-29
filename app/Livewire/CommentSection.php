<?php

namespace App\Livewire;

use App\Models\AlternativeComment;
use App\Models\OpenSourceAlternative;
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

        // Basic spam heuristics
        if (preg_match('/https?:\/\//i', $data['body']) && substr_count(strtolower($data['body']), 'http') > 2) {
            $this->message = 'Too many links — comment blocked.';

            return;
        }

        RateLimiter::hit($key, 3600);

        $approved = Auth::check(); // members publish immediately; guests need approval

        AlternativeComment::create([
            'open_source_alternative_id' => $this->alternativeId,
            'user_id' => Auth::id(),
            'author_name' => $data['author_name'],
            'author_email' => Auth::check() ? Auth::user()->email : ($data['author_email'] ?? null),
            'body' => strip_tags($data['body']),
            'is_approved' => $approved,
            'is_hidden' => false,
            'ip_address' => request()->ip(),
        ]);

        $this->body = '';
        $this->message = $approved
            ? 'Comment published.'
            : 'Thanks — your comment is awaiting moderation.';
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
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get();
            } catch (\Throwable) {
            }
        }

        return view('livewire.comment-section', [
            'comments' => $comments,
            'isLoggedIn' => Auth::check(),
        ]);
    }
}
