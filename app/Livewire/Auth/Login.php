<?php

namespace App\Livewire\Auth;

use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = true;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('account'), navigate: true);
        }
    }

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            throw ValidationException::withMessages([
                'email' => 'Too many attempts. Try again in a few minutes.',
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 300);
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($key);
        request()->session()->regenerate();

        $user = Auth::user();
        try {
            app(FavoriteService::class)->mergeSessionIntoUser($user);
            app(SavedDomainService::class)->mergeSessionIntoUser($user->id);
        } catch (\Throwable) {
        }

        $this->redirect(route('account'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.app', [
                'title' => 'Sign in | Alternova',
                'description' => 'Sign in to sync favorites across devices.',
                'robots' => 'noindex,follow',
            ]);
    }
}
