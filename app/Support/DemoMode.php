<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class DemoMode
{
    protected static ?bool $resolved = null;

    /**
     * Demo mode must work even when `php artisan config:cache` is used.
     * After config:cache, Laravel's env() returns null outside config files.
     */
    public static function enabled(): bool
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        // 1) Config (works if config was cached WITH DEMO_MODE=true)
        if (filter_var(config('demo.enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return self::$resolved = true;
        }

        // 2) env() only works when config is NOT cached
        if (filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN)) {
            return self::$resolved = true;
        }

        // 3) Read .env file directly (works on Hostinger even with config:cache)
        if (self::envFileSaysTrue('DEMO_MODE')) {
            return self::$resolved = true;
        }

        return self::$resolved = false;
    }

    protected static function envFileSaysTrue(string $key): bool
    {
        $path = base_path('.env');
        if (! is_readable($path)) {
            return false;
        }

        $content = @file_get_contents($path);
        if (! is_string($content) || $content === '') {
            return false;
        }

        // Match DEMO_MODE=true / 1 / yes / on (optional quotes/spaces)
        $pattern = '/^\s*'.preg_quote($key, '/').'\s*=\s*["\']?(true|1|yes|on)["\']?\s*$/mi';

        return (bool) preg_match($pattern, $content);
    }

    public static function message(): string
    {
        $fromConfig = config('demo.message');
        if (is_string($fromConfig) && $fromConfig !== '') {
            return $fromConfig;
        }

        if (self::envFileValue('DEMO_MESSAGE') !== null) {
            return self::envFileValue('DEMO_MESSAGE');
        }

        return "Can't Make Edit or Create in Demo Version";
    }

    protected static function envFileValue(string $key): ?string
    {
        $path = base_path('.env');
        if (! is_readable($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if (! is_string($content)) {
            return null;
        }

        if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=\s*(.*)$/mi', $content, $m)) {
            $val = trim($m[1]);
            $val = trim($val, "\"'");

            return $val !== '' ? $val : null;
        }

        return null;
    }

    public static function fail(?string $context = null): void
    {
        $message = self::message();

        try {
            Notification::make()
                ->title($message)
                ->body('You can explore every screen. Changes are not saved in the demo.')
                ->warning()
                ->persistent()
                ->send();
        } catch (\Throwable) {
        }

        throw ValidationException::withMessages([
            'demo' => $message,
        ]);
    }

    public static function guardWrite(?string $context = null): void
    {
        if (! self::enabled()) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        self::fail($context);
    }

    /**
     * @return list<string>
     */
    public static function blockedLivewireMethods(): array
    {
        return [
            'create',
            'createAnother',
            'save',
            'delete',
            'forceDelete',
            'restore',
            'replicate',
            'callMountedAction',
            'callMountedFormComponentAction',
            'callMountedTableAction',
            'callTableAction',
            'callTableBulkAction',
            'callMountedTableBulkAction',
            'saveFormComponentOnly',
            'publish',
            'unpublish',
            'sync',
            'generate',
            'import',
            'submit',
            'update',
            'store',
            'destroy',
            'attach',
            'detach',
            'associate',
            'dissociate',
        ];
    }

    public static function registerEloquentGuards(): void
    {
        $block = function ($model): void {
            if (! self::enabled()) {
                return;
            }

            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            self::fail(class_basename($model));
        };

        \Illuminate\Database\Eloquent\Model::creating($block);
        \Illuminate\Database\Eloquent\Model::updating($block);
        \Illuminate\Database\Eloquent\Model::saving($block);
        \Illuminate\Database\Eloquent\Model::deleting($block);
    }
}
