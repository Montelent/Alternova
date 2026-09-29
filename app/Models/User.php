<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_MEMBER = 'member';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    public function canAccessPanel(Panel $panel): bool
    {
        // Public members must not enter Filament admin
        if (($this->role ?? '') === self::ROLE_MEMBER) {
            return false;
        }

        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_EDITOR], true)
            || $this->role === null;
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
