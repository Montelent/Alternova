<?php

namespace App\Livewire\Hooks;

use App\Support\DemoMode;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login;
use Livewire\ComponentHook;

/**
 * Demo mode: block only real write methods (create/save/delete).
 * Do NOT blanket-block callMountedAction — that breaks Filament UI
 * (modals, header actions, navigation) and makes Create look dead.
 * Persistence is still blocked by Eloquent wildcards in DemoMode.
 */
class DemoModeHook extends ComponentHook
{
    public function call(mixed $method = null, mixed $params = null, mixed $returnEarly = null): void
    {
        if (! DemoMode::enabled()) {
            return;
        }

        if ($this->component instanceof Login) {
            return;
        }

        if (! is_string($method)) {
            return;
        }

        // Only the methods that actually persist a record from Create/Edit pages
        $writeMethods = [
            'create',
            'createAnother',
            'save',
            'delete',
            'forceDelete',
            'restore',
            'replicate',
        ];

        if (! in_array($method, $writeMethods, true)) {
            return;
        }

        $message = DemoMode::message();

        try {
            Notification::make()
                ->title($message)
                ->body('You can open every screen in this demo. Changes are not saved.')
                ->warning()
                ->persistent()
                ->send();
        } catch (\Throwable) {
        }

        try {
            $this->component->addError('demo', $message);
        } catch (\Throwable) {
        }

        if (is_callable($returnEarly)) {
            $returnEarly(null);
        }
    }
}
