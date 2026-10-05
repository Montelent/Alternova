<?php

namespace App\Livewire\Hooks;

use App\Support\DemoMode;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login;
use Livewire\ComponentHook;

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

        $blocked = DemoMode::blockedLivewireMethods();
        $isBlocked = in_array($method, $blocked, true);

        // Also catch Filament actions named like delete / save / create
        if (! $isBlocked && in_array($method, ['callMountedAction', 'callMountedTableAction', 'callTableAction', 'callMountedFormComponentAction'], true)) {
            $isBlocked = true;
        }

        if (! $isBlocked) {
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
