<?php

namespace App\Support;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class DemoMode
{
    public static function enabled(): bool
    {
        // Prefer config (after config:clear). Fall back to env so a stale
        // config cache cannot leave the demo site writable by accident.
        if (config()->has('demo.enabled')) {
            return filter_var(config('demo.enabled'), FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(env('DEMO_MODE', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function message(): string
    {
        return (string) (config('demo.message')
            ?: env('DEMO_MESSAGE', "Can't Make Edit or Create in Demo Version"));
    }

    /**
     * Block browser-originated writes. Artisan still works.
     */
    public static function guardWrite(?string $context = null): void
    {
        if (! self::enabled()) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        if (self::isAuthPath(request()->path())) {
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
            'demo' => $message.($context ? " ({$context})" : ''),
        ]);
    }

    public static function isAuthPath(string $path): bool
    {
        $path = trim($path, '/');

        return str_starts_with($path, 'admin/login')
            || str_starts_with($path, 'admin/logout')
            || $path === 'login'
            || $path === 'logout';
    }

    /**
     * Livewire method names that create / update / delete data in Filament.
     *
     * @return list<string>
     */
    public static function blockedLivewireMethods(): array
    {
        return [
            'create',
            'createAnother',
            'save',
            'saveFormComponent',
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
            'mountTableAction',
            'mountFormComponentAction',
            'install',
            'publish',
            'unpublish',
            'sync',
            'generate',
            'import',
            'export',
            'run',
            'submit',
            'update',
            'store',
            'destroy',
            'detach',
            'attach',
            'associate',
            'dissociate',
        ];
    }

    public static function livewireRequestIsMutation(\Illuminate\Http\Request $request): bool
    {
        $components = $request->input('components', []);
        if (! is_array($components)) {
            return false;
        }

        $blocked = array_fill_keys(self::blockedLivewireMethods(), true);

        foreach ($components as $component) {
            if (! is_array($component)) {
                continue;
            }

            // Never block pure Filament login
            $snapshot = (string) ($component['snapshot'] ?? '');
            if (str_contains($snapshot, 'Filament\\Pages\\Auth\\Login')
                || str_contains($snapshot, 'Pages\\Auth\\Login')) {
                continue;
            }

            foreach ($component['calls'] ?? [] as $call) {
                $method = is_array($call) ? (string) ($call['method'] ?? '') : '';
                if ($method !== '' && isset($blocked[$method])) {
                    return true;
                }
            }

            // Some Filament builds put the method under updates
            foreach (array_keys($component['updates'] ?? []) as $key) {
                if (is_string($key) && str_contains(strtolower($key), 'password')) {
                    // password field typing is fine
                    continue;
                }
            }
        }

        return false;
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
