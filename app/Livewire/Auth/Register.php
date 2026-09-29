<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('account'), navigate: true);
        }
    }

    public function register(): void
    {
        $key = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many registrations from this network.');

            return;
        }

        $data = $this->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        RateLimiter::hit($key, 3600);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_MEMBER,
        ]);

        Auth::login($user, true);
        request()->session()->regenerate();

        try {
            app(FavoriteService::class)->mergeSessionIntoUser($user);
            app(SavedDomainService::class)->mergeSessionIntoUser($user->id);
        } catch (\Throwable) {
        }

        $this->redirect(route('account'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.app', [
                'title' => 'Create account | Alternova',
                'description' => 'Create a free Alternova account to sync favorites across devices.',
                'robots' => 'noindex,follow',
            ]);
    }
}
