<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use App\Services\SeoManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $data = $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
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
        $robots = app(SeoManager::class)->privateRobots();

        return view('livewire.auth.register')
            ->layout('layouts.app', [
                'title' => 'Create account | Alternova',
                'description' => 'Create an Alternova account to save favorites and watchlists.',
                'robots' => $robots,
                'canonical' => route('register'),
            ]);
    }
}
