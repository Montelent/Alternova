<?php

namespace App\Livewire;

use App\Models\ApiKey;
use App\Models\ContactMessage;
use App\Models\OpenSourceAlternative;
use App\Models\SavedDomain;
use App\Models\User;
use App\Services\FavoriteService;
use App\Services\SeoManager;
use App\Services\WatchlistService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class AccountPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $apiKeyName = '';

    public string $profileMessage = '';

    public string $passwordMessage = '';

    public string $apiKeyMessage = '';

    public ?string $newPlainKey = null;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        /** @var User $user */
        $user = Auth::user();
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
    }

    public function updateProfile(): void
    {
        $this->profileMessage = '';
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->forceFill([
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
        ])->save();

        $this->profileMessage = 'Profile saved.';
    }

    public function updatePassword(): void
    {
        $this->passwordMessage = '';
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill(['password' => Hash::make($this->password)])->save();
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->passwordMessage = 'Password updated.';
    }

    public function createApiKey(): void
    {
        $this->apiKeyMessage = '';
        $this->newPlainKey = null;

        if (! $this->apiKeysReady()) {
            $this->apiKeyMessage = 'API keys are not available on this install yet.';

            return;
        }

        $this->validate(['apiKeyName' => ['required', 'string', 'max:80']]);

        /** @var User $user */
        $user = Auth::user();
        $issued = ApiKey::issue($user, trim($this->apiKeyName));
        $this->newPlainKey = $issued['plain'];
        $this->apiKeyName = '';
        $this->apiKeyMessage = 'API key created. Copy it now — it will not be shown again.';
    }

    public function revokeApiKey(int $id): void
    {
        if (! $this->apiKeysReady()) {
            return;
        }

        $key = ApiKey::query()->where('user_id', Auth::id())->where('id', $id)->first();
        if ($key && $key->revoked_at === null) {
            $key->revoke();
            $this->apiKeyMessage = 'API key revoked.';
            $this->newPlainKey = null;
        }
    }

    public function removeWatch(int $alternativeId): void
    {
        app(WatchlistService::class)->unwatch($alternativeId);
    }

    public function removeFavorite(int $alternativeId): void
    {
        $alt = OpenSourceAlternative::query()->find($alternativeId);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function removeDomain(int $id): void
    {
        SavedDomain::query()->where('user_id', Auth::id())->where('id', $id)->delete();
    }

    protected function apiKeysReady(): bool
    {
        try {
            return Schema::hasTable('api_keys');
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return Collection<int, ApiKey> */
    protected function loadApiKeys(): Collection
    {
        if (! $this->apiKeysReady()) {
            return collect();
        }

        return ApiKey::query()->where('user_id', Auth::id())->orderByDesc('created_at')->limit(50)->get();
    }

    /** @return Collection<int, OpenSourceAlternative> */
    protected function loadFavorites(): Collection
    {
        try {
            return app(FavoriteService::class)->list();
        } catch (\Throwable) {
            return collect();
        }
    }

    /** @return Collection<int, OpenSourceAlternative> */
    protected function loadWatched(): Collection
    {
        try {
            return app(WatchlistService::class)->listFor(Auth::user());
        } catch (\Throwable) {
            return collect();
        }
    }

    /** @return Collection<int, SavedDomain> */
    protected function loadDomains(): Collection
    {
        try {
            if (! Schema::hasTable('saved_domains')) {
                return collect();
            }

            return SavedDomain::query()->where('user_id', Auth::id())->orderByDesc('created_at')->limit(50)->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    /** @return Collection<int, ContactMessage> */
    protected function loadTickets(): Collection
    {
        try {
            if (! Schema::hasTable('contact_messages')) {
                return collect();
            }

            $email = Auth::user()->email;

            return ContactMessage::query()
                ->where(function ($q) use ($email) {
                    $q->where('user_id', Auth::id());
                    if ($email) {
                        $q->orWhere('email', $email);
                    }
                })
                ->orderByDesc('updated_at')
                ->limit(30)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $robots = app(SeoManager::class)->privateRobots();

        return view('livewire.account-page', [
            'user' => $user,
            'apiKeys' => $this->loadApiKeys(),
            'favorites' => $this->loadFavorites(),
            'watched' => $this->loadWatched(),
            'domains' => $this->loadDomains(),
            'tickets' => $this->loadTickets(),
        ])->layout('layouts.app', [
            'title' => 'Your account | '.config('app.name', 'Alternova'),
            'description' => 'Manage your account, tickets, favorites, and watchlist.',
            'robots' => $robots,
            'canonical' => route('account'),
        ]);
    }
}
