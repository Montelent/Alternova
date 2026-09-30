<?php

namespace App\Livewire;

use App\Models\AlternativeProsCon;
use App\Models\OpenSourceAlternative;
use App\Services\ProsConsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ProsConsSection extends Component
{
    public int $alternativeId;

    public string $type = 'pro';

    public string $body = '';

    public string $authorName = '';

    public string $message = '';

    public bool $showForm = false;

    public function mount(int $alternativeId): void
    {
        $this->alternativeId = $alternativeId;
        if (Auth::check()) {
            $this->authorName = (string) (Auth::user()->name ?? '');
        }
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
        $this->message = '';
    }

    public function submit(): void
    {
        $key = 'proscons-submit:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->message = 'Too many submissions. Try later.';

            return;
        }
        RateLimiter::hit($key, 3600);

        $this->validate([
            'type' => 'required|in:pro,con',
            'body' => 'required|string|min:8|max:280',
            'authorName' => 'nullable|string|max:80',
        ]);

        $alt = OpenSourceAlternative::query()->find($this->alternativeId);
        if (! $alt) {
            $this->message = 'Alternative not found.';

            return;
        }

        $result = app(ProsConsService::class)->submit(
            $alt,
            $this->type,
            $this->body,
            $this->authorName ?: null,
            Auth::user()?->email,
            request()->ip(),
            Auth::user()
        );

        $this->message = $result['message'];
        if ($result['ok']) {
            $this->body = '';
            $this->showForm = false;
        }
    }

    public function upvote(int $id): void
    {
        $key = 'proscons-vote:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $this->message = 'Too many votes. Try later.';

            return;
        }
        RateLimiter::hit($key, 3600);

        $item = AlternativeProsCon::query()->find($id);
        if (! $item || (int) $item->open_source_alternative_id !== $this->alternativeId) {
            return;
        }

        $service = app(ProsConsService::class);
        $voterKey = $service->voterKey(session()->getId(), request()->ip());
        $result = $service->upvote($item, $voterKey, request()->ip());
        $this->message = $result['message'];
    }

    public function render()
    {
        $service = app(ProsConsService::class);
        $alt = OpenSourceAlternative::query()->find($this->alternativeId);
        $lists = $alt ? $service->listsFor($alt) : ['pros' => collect(), 'cons' => collect()];

        $voterKey = $service->voterKey(session()->getId(), request()->ip());
        $votedIds = [];
        foreach ($lists['pros']->concat($lists['cons']) as $item) {
            if ($service->hasVoted($item->id, $voterKey)) {
                $votedIds[] = $item->id;
            }
        }

        return view('livewire.pros-cons-section', [
            'pros' => $lists['pros'],
            'cons' => $lists['cons'],
            'votedIds' => $votedIds,
            'ready' => $service->ready(),
        ]);
    }
}
