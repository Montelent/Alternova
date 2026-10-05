<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;

class DemoMode
{
    protected static ?bool $resolved = null;

    /**
     * Works even when config:cache is used (env() is null outside config files).
     */
    public static function enabled(): bool
    {
        if (self::$resolved !== null) {
            return self::$resolved;
        }

        if (filter_var(config('demo.enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return self::$resolved = true;
        }

        if (filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN)) {
            return self::$resolved = true;
        }

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

        $pattern = '/^\s*'.preg_quote($key, '/').'\s*=\s*["\']?(true|1|yes|on)["\']?\s*$/mi';

        return (bool) preg_match($pattern, $content);
    }

    public static function message(): string
    {
        $fromConfig = config('demo.message');
        if (is_string($fromConfig) && $fromConfig !== '') {
            return $fromConfig;
        }

        $fromEnvFile = self::envFileValue('DEMO_MESSAGE');
        if ($fromEnvFile !== null) {
            return $fromEnvFile;
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

    /**
     * IMPORTANT: Model::saving() on the base Model class only listens for
     * Illuminate\Database\Eloquent\Model itself — NOT OpenSourceAlternative,
     * Page, User, etc. Use eloquent.*: * wildcards so every model is blocked.
     */
    public static function registerEloquentGuards(): void
    {
        $block = function (object $model): void {
            if (! self::enabled()) {
                return;
            }

            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }

            self::fail(class_basename($model));
        };

        Event::listen('eloquent.creating: *', $block);
        Event::listen('eloquent.updating: *', $block);
        Event::listen('eloquent.saving: *', $block);
        Event::listen('eloquent.deleting: *', $block);
    }
}
