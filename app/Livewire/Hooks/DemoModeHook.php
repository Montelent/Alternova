<?php

namespace App\Livewire\Hooks;

use App\Support\DemoMode;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login;
use Livewire\ComponentHook;

/**
 * Intercept Filament/Livewire create-save-delete calls in DEMO_MODE.
 * Keeps the user on the same screen and shows a notification (no full reload).
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

        if (! is_string($method) || ! in_array($method, DemoMode::blockedLivewireMethods(), true)) {
            return;
        }

        $message = DemoMode::message();

        try {
            Notification::make()
                ->title($message)
                ->body('You can explore every screen. Changes are not saved in the demo.')
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
