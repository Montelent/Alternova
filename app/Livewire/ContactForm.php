<?php

namespace App\Livewire;

use App\Models\ContactMessage;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public string $website = ''; // honeypot

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

        $data = $this->validate();
        unset($data['website']);

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
