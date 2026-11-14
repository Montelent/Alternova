<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use App\Services\SupportTicketService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SupportTicketShow extends Component
{
    #[Locked]\n    public ContactMessage $ticket;

    public string $replyBody = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    public function mount(string $publicId): void
    {
        $ticket = ContactMessage::query()
            ->where('public_id', $publicId)
            ->firstOrFail();

        $queryToken = request()->query('token');
        $tokenFromQuery = is_string($queryToken) && $queryToken !== '';
        $token = $tokenFromQuery ? $queryToken : session('support_ticket_token_'.$ticket->id);

        $user = Auth::user();

        if (! $ticket->canBeViewedBy($user, is_string($token) ? $token : null)) {
            abort(403, 'You do not have access to this ticket. Sign in with the same email used on the ticket, or use the link from your email.');
        }

        // Store token in session so later visits without query still work
        if (is_string($token) && $token !== '') {
            session(['support_ticket_token_'.$ticket->id => $token]);

            // Only strip a token that was present in the incoming query string.
            // A token restored from the session must not trigger another redirect.
            if ($tokenFromQuery) {
                $this->redirect(route('support.show', ['publicId' => $ticket->public_id]), navigate: false);

                return;
            }
        }

        $this->ticket = $ticket->load(['publicReplies']);
    }

    public function sendReply(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (! Auth::check()) {
            $this->errorMessage = 'Sign in with the same email as this ticket to reply.';

            return;
        }

        $this->validate([
            'replyBody' => 'required|string|min:2|max:5000',
        ]);

        $user = Auth::user();
        $reply = app(SupportTicketService::class)->replyAsUser($this->ticket, $this->replyBody, $user);

        if (! $reply) {
            $this->errorMessage = 'Could not post reply. Make sure you are signed in with the ticket email and the ticket is still open.';

            return;
        }

        $this->replyBody = '';
        $this->successMessage = 'Reply sent. Support will be notified by email.';
        $this->ticket = $this->ticket->fresh(['publicReplies']);
    }

    public function render()
    {
        $id = $this->ticket->public_id ?: '#'.$this->ticket->id;

        return view('livewire.support-ticket-show', [
            'canReply' => Auth::check() && $this->ticket->canBeRepliedBy(Auth::user()),
            'isLoggedIn' => Auth::check(),
        ])->layout('layouts.app', [
            'title' => 'Ticket '.$id.' | '.config('app.name', 'Alternova'),
            'description' => 'Support ticket conversation.',
            'robots' => 'noindex,nofollow',
            'canonical' => url('/support/'.$this->ticket->public_id),
        ]);
    }
}
