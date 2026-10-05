<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class DemoMode
{
    public static function enabled(): bool
    {
        // Read env every time so a stale config:cache cannot leave the demo writable.
        $fromEnv = filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN);
        if ($fromEnv) {
            return true;
        }

        return filter_var(config('demo.enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function message(): string
    {
        $msg = env('DEMO_MESSAGE');
        if (is_string($msg) && $msg !== '') {
            return $msg;
        }

        return (string) config(
            'demo.message',
            "Can't Make Edit or Create in Demo Version"
        );
    }

    public static function guardWrite(?string $context = null): void
    {
        if (! self::enabled()) {
            return;
        }

        // Allow artisan / queue / tinker to maintain demo content
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        self::fail($context);
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
     * Always register listeners. Each write re-checks enabled().
     * (Registering only when enabled at boot is why saves still worked.)
     */
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
