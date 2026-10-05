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
            'Demo mode is on. You can explore the admin and public pages, but changes are not saved.'
        );
    }

    /**
     * Block browser-originated writes. Artisan / tinker / queue workers still run.
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

        if (class_exists(Notification::class) && app()->bound('filament')) {
            try {
                Notification::make()
                    ->title('Demo mode')
                    ->body(self::message())
                    ->warning()
                    ->send();
            } catch (\Throwable) {
            }
        }

        throw ValidationException::withMessages([
            'demo' => self::message().($context ? " ({$context})" : ''),
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

        return is_string($payload) && (
            str_contains($payload, 'Login')
            || str_contains($payload, 'logout')
        );
    }

    public static function registerEloquentGuards(): void
    {
        if (! self::enabled()) {
            return;
        }

        $block = function ($model): void {
            if ($model instanceof \App\Models\User) {
                return;
            }

            self::guardWrite(class_basename($model));
        };

        \Illuminate\Database\Eloquent\Model::creating($block);
        \Illuminate\Database\Eloquent\Model::updating($block);
        \Illuminate\Database\Eloquent\Model::saving($block);
        \Illuminate\Database\Eloquent\Model::deleting($block);
    }
}
