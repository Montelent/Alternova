<?php

namespace App\Livewire;

use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class AccountPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $profileMessage = '';

    public string $passwordMessage = '';

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $user = Auth::user();
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
    }

    public function updateProfile(): void
    {
        $user = Auth::user();

        $data = $this->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|max:190|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $this->profileMessage = 'Profile updated.';
        $this->passwordMessage = '';
    }

    public function updatePassword(): void
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');

            return;
        }

        $user->update([
            'password' => $this->password,
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->passwordMessage = 'Password changed.';
        $this->profileMessage = '';
    }

    public function removeFavorite(int $id): void
    {
        $alt = \App\Models\OpenSourceAlternative::query()->find($id);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function removeDomain(int $id): void
    {
        $row = \App\Models\SavedDomain::query()->find($id);
        if ($row && (int) $row->user_id === (int) Auth::id()) {
            $row->delete();
        }
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.account-page', [
            'user' => $user,
            'favorites' => app(FavoriteService::class)->list(),
            'domains' => app(SavedDomainService::class)->list(),
        ])->layout('layouts.app', [
            'title' => 'Your account | Alternova',
            'description' => 'Manage profile, password, favorites, and saved domains.',
            'robots' => 'noindex,follow',
        ]);
    }
}
