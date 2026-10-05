<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class DemoMode
{
    public static function enabled(): bool
    {
        return (bool) config('demo.enabled', false);
    }

    public static function message(): string
    {
        return (string) config(
            'demo.message',
            "Can't Make Edit or Create in Demo Version"
        );
    }

    /**
     * Block browser-originated writes. Artisan / tinker still run.
     * Create/Edit pages stay reachable; only persistence is stopped.
     */
    public static function guardWrite(?string $context = null): void
    {
        if (! self::enabled()) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $path = request()->path();
        if (self::isAuthPath($path)) {
            return;
        }

        $message = self::message();

        try {
            Notification::make()
                ->title($message)
                ->body('You can explore every screen in this demo. Changes are not saved.')
                ->warning()
                ->persistent()
                ->send();
        } catch (\Throwable) {
        }

        throw ValidationException::withMessages([
            'demo' => $message,
        ]);
    }

    public static function isAuthPath(string $path): bool
    {
        $path = trim($path, '/');

        return str_starts_with($path, 'admin/login')
            || str_starts_with($path, 'admin/logout')
            || $path === 'login'
            || $path === 'logout'
            || (str_contains($path, 'livewire/update') && self::isAuthLivewireRequest());
    }

    protected static function isAuthLivewireRequest(): bool
    {
        $components = request()->input('components', []);
        if (! is_array($components)) {
            return false;
        }

        $payload = json_encode($components);

        // Only pure login/logout components — not UserResource edit forms
        return is_string($payload)
            && (
                str_contains($payload, 'Filament\\Pages\\Auth\\Login')
                || str_contains($payload, 'Filament\Pages\Auth\Login')
                || str_contains($payload, '"logout"')
            )
            && ! str_contains($payload, 'UserResource');
    }

    public static function registerEloquentGuards(): void
    {
        if (! self::enabled()) {
            return;
        }

        $block = function ($model): void {
            self::guardWrite(class_basename($model));
        };

        \Illuminate\Database\Eloquent\Model::creating($block);
        \Illuminate\Database\Eloquent\Model::updating($block);
        \Illuminate\Database\Eloquent\Model::saving($block);
        \Illuminate\Database\Eloquent\Model::deleting($block);
    }
}
