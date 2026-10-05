<?php

namespace App\Http\Middleware;

use App\Support\DemoMode;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safety net for non-Livewire admin POSTs only.
 * Livewire/Filament Save is handled by DemoModeHook (no page refresh).
 */
class PreventDemoWrites
{
    public function handle(Request \$request, Closure \$next): Response
    {
        if (! DemoMode::enabled()) {
            return \$next(\$request);
        }

        \$isLivewire = \$request->is('livewire/*')
            || str_contains(\$request->path(), 'livewire/update');

        // Do NOT intercept Livewire here — that caused full page reloads.
        if (\$isLivewire) {
            return \$next(\$request);
        }

        if (\$request->is('admin/*')
            && ! \$request->is('admin/login', 'admin/logout')
            && in_array(\$request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
        ) {
            \$message = DemoMode::message();

            try {
                Notification::make()
                    ->title(\$message)
                    ->warning()
                    ->persistent()
                    ->send();
            } catch (\Throwable) {
            }

            return back()
                ->withErrors(['demo' => \$message])
                ->with('error', \$message);
        }

        return \$next(\$request);
    }
}
