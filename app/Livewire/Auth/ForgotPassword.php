<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ForgotPassword extends Component
{
    public string $email = '';

    public string $status = '';

    public function sendResetLink(): void
    {
        $this->validate([
            'email' => 'required|email',
        ]);

        $key = 'forgot-password:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many reset requests. Try again later.');

            return;
        }

        RateLimiter::hit($key, 600);

        $status = Password::sendResetLink(['email' => $this->email]);

        // Keep the response identical whether the account exists, avoiding
        // a password-reset endpoint that can be used to enumerate users.
        $this->status = 'If that email is registered, a reset link is on its way.';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.auth.forgot-password')
            ->layout('layouts.app', [
                'title' => 'Forgot password | Alternova',
                'description' => 'Request a password reset link for your Alternova account.',
                'robots' => 'noindex,follow',
            ]);
    }
}
