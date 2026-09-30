<?php

namespace App\Livewire;

use App\Models\ApiKey;
use App\Services\FavoriteService;
use App\Services\SavedDomainService;
use App\Services\WatchlistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

    public string $apiKeyName = 'Default';

    public string $apiKeyMessage = '';

    public ?string $newPlainKey = null;

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

    public function createApiKey(): void
    {
        if (! Schema::hasTable('api_keys')) {
            $this->apiKeyMessage = 'Run migrations to enable API keys.';

            return;
        }

        $this->validate([
            'apiKeyName' => 'required|string|max:80',
        ]);

        $user = Auth::user();
        $activeCount = ApiKey::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->count();

        if ($activeCount >= 5) {
            $this->apiKeyMessage = 'Maximum of 5 active keys. Revoke one first.';

            return;
        }

        $issued = ApiKey::issue($user, $this->apiKeyName);
        $this->newPlainKey = $issued['plain'];
        $this->apiKeyMessage = 'Key created — copy it now. It will not be shown again.';
        $this->apiKeyName = 'Default';
    }

    public function revokeApiKey(int $id): void
    {
        $key = ApiKey::query()
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->first();

        if ($key && $key->isActive()) {
            $key->revoke();
            $this->apiKeyMessage = 'Key revoked.';
            $this->newPlainKey = null;
        }
    }

    public function removeFavorite(int $id): void
    {
        $alt = \App\Models\OpenSourceAlternative::query()->find($id);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function removeWatch(int $id): void
    {
        app(WatchlistService::class)->unwatch($id);
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

        $apiKeys = collect();
        try {
            if (Schema::hasTable('api_keys')) {
                $apiKeys = ApiKey::query()
                    ->where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->get();
            }
        } catch (\Throwable) {
        }

        $watched = collect();
        try {
            $watched = app(WatchlistService::class)->listFor($user);
        } catch (\Throwable) {
        }

        return view('livewire.account-page', [
            'user' => $user,
            'favorites' => app(FavoriteService::class)->list(),
            'watched' => $watched,
            'domains' => app(SavedDomainService::class)->list(),
            'apiKeys' => $apiKeys,
        ])->layout('layouts.app', [
            'title' => 'Your account | Alternova',
            'description' => 'Manage profile, password, API keys, favorites, watchlist, and saved domains.',
            'robots' => 'noindex,follow',
        ]);
    }
}
