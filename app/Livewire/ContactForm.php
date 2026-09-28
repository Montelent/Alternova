<?php

namespace App\Livewire;

use App\Models\ContactMessage;
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

        ContactMessage::create([
            ...$data,
            'status' => 'unread',
            'ip_address' => request()->ip(),
        ]);

        $this->reset(['name', 'email', 'subject', 'message', 'website']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.contact-form')
            ->layout('layouts.app', [
                'title' => 'Contact — Alternova',
                'description' => 'Contact the Alternova team for feedback, corrections, or partnership inquiries.',
            ]);
    }
}
