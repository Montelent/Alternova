<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable implements FilamentUser, CanResetPasswordContract
{
    use CanResetPassword, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'api_enabled',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'api_enabled' => 'boolean',
        ];
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(FavoriteAlternative::class);
    }

    public function savedDomains(): HasMany
    {
        return $this->hasMany(SavedDomain::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function watchedAlternatives(): HasMany
    {
        return $this->hasMany(WatchedAlternative::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        if (($this->role ?? '') === self::ROLE_MEMBER) {
            return false;
        }

        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR], true)
            || $this->role === null;
    }

    public function isActive(): bool
    {
        // Default true if column missing / null (legacy installs)
        return ($this->is_active ?? true) === true;
    }

    /**
     * Whether this account may use the public API (keys still must be valid).
     * Defaults to true when the column is missing (pre-migration installs).
     */
    public function hasApiAccess(): bool
    {
        try {
            if (! Schema::hasColumn('users', 'api_enabled')) {
                return true;
            }
        } catch (\Throwable) {
            return true;
        }

        return ($this->api_enabled ?? true) === true;
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? self::ROLE_ADMIN) === self::ROLE_ADMIN;
    }

    public function isEditor(): bool
    {
        return ($this->role ?? '') === self::ROLE_EDITOR;
    }

    public function isMember(): bool
    {
        return ($this->role ?? '') === self::ROLE_MEMBER;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageSystem(): bool
    {
        return $this->isAdmin();
    }
}
