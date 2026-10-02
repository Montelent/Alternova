<?php

namespace App\Livewire;

use App\Services\SupportTicketService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $website = '';

    public bool $sent = false;

    public ?string $ticketPublicId = null;

    public ?string $ticketUrl = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->name = (string) (Auth::user()->name ?? '');
            $this->email = (string) (Auth::user()->email ?? '');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180',
            'subject' => 'nullable|string|max:180',
            'message' => 'required|string|min:10|max:5000',
            'website' => 'max:0',
        ];
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            $this->sent = true;

            return;
        }

        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', 'Too many messages. Try again in '.$seconds.' seconds.');

            return;
        }

        $data = $this->validate();
        unset($data['website']);

        RateLimiter::hit($key, 3600);

        $ticket = app(SupportTicketService::class)->createTicket([
            ...$data,
            'ip_address' => request()->ip(),
        ], Auth::user());

        $this->ticketPublicId = $ticket->public_id;
        $this->ticketUrl = $ticket->publicUrl();
        $this->reset(['name', 'email', 'subject', 'message', 'website']);
        if (Auth::check()) {
            $this->name = (string) Auth::user()->name;
            $this->email = (string) Auth::user()->email;
        }
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form')
            ->layout('layouts.app', [
                'title' => 'Contact — '.config('app.name', 'Alternova'),
                'description' => 'Open a support ticket for feedback, corrections, or partnership inquiries.',
                'canonical' => route('contact'),
            ]);
    }
}
