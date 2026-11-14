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
use App\Support\ApiSettings;
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

    public string $profile_current_password = '';

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
        abort_unless(Auth::check(), 401);
        $this->profileMessage = '';
        /** @var User $user */
        $user = Auth::user();
        $newEmail = strtolower(trim($this->email));
        $emailChanged = $newEmail !== strtolower((string) $user->email);

        // This app does not yet implement signed email-verification links.
        // Never let a privileged account change its verified login identity
        // while retaining the old email_verified_at timestamp.
        if ($emailChanged && in_array($user->role, [User::ROLE_ADMIN, User::ROLE_EDITOR], true)) {
            $this->addError('email', 'For security, administrator and editor email changes must be handled by another administrator until email re-verification is available.');

            return;
        }

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        if ($emailChanged) {
            $rules['profile_current_password'] = ['required', 'current_password'];
        }

        $this->validate($rules);

        $attributes = [
            'name' => trim($this->name),
            'email' => $newEmail,
        ];

        if ($emailChanged) {
            $attributes['email_verified_at'] = null;
        }

        $user->forceFill($attributes)->save();

        $this->email = $newEmail;
        $this->reset('profile_current_password');
        $this->profileMessage = 'Profile saved.';
    }

    public function updatePassword(): void
    {
        abort_unless(Auth::check(), 401);
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
        abort_unless(Auth::check(), 401);
        $this->apiKeyMessage = '';
        $this->newPlainKey = null;

        if (! $this->apiKeysReady()) {
            $this->apiKeyMessage = 'API keys are not available on this install yet.';

            return;
        }

        if (! ApiSettings::isGloballyEnabled()) {
            $this->apiKeyMessage = 'The public API is disabled by the site administrator.';

            return;
        }

        /** @var User $user */
        $user = Auth::user();
        if (! $user->hasApiAccess()) {
            $this->apiKeyMessage = 'API access is disabled for your account. Contact the site administrator.';

            return;
        }

        $this->validate(['apiKeyName' => ['required', 'string', 'max:80']]);

        $issued = ApiKey::issue($user, trim($this->apiKeyName));
        $this->newPlainKey = $issued['plain'];
        $this->apiKeyName = '';
        $this->apiKeyMessage = 'API key created. Copy it now — it will not be shown again.';
    }

    public function revokeApiKey(int $id): void
    {
        abort_unless(Auth::check(), 401);
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
        abort_unless(Auth::check(), 401);
        app(WatchlistService::class)->unwatch($alternativeId);
    }

    public function removeFavorite(int $alternativeId): void
    {
        abort_unless(Auth::check(), 401);
        $alt = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->find($alternativeId);
        if ($alt) {
            app(FavoriteService::class)->toggle($alt);
        }
    }

    public function removeDomain(int $id): void
    {
        abort_unless(Auth::check(), 401);
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

            $user = Auth::user();

            return ContactMessage::query()
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);

                    // Guest tickets may be associated by email only after the
                    // account has proved control of that mailbox.
                    if ($user->email_verified_at !== null && $user->email) {
                        $q->orWhere('email', $user->email);
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
