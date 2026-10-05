<?php

namespace App\Providers;

use App\Livewire\Hooks\DemoModeHook;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Observers\OpenSourceAlternativeObserver;
use App\Observers\ProprietaryToolObserver;
use App\Support\DemoMode;
use App\Support\IntegrationsSettings;
use App\Support\MailSettings;
use App\View\Composers\CmsNavComposer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sync config from live detection (handles config:cache + .env)
        if (DemoMode::enabled()) {
            config(['demo.enabled' => true]);
        }

        DemoMode::registerEloquentGuards();

        if (class_exists(Livewire::class)) {
            Livewire::componentHook(DemoModeHook::class);
        }

        View::composer(['layouts.app', 'welcome'], CmsNavComposer::class);

        OpenSourceAlternative::observe(OpenSourceAlternativeObserver::class);
        ProprietaryTool::observe(ProprietaryToolObserver::class);

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        try {
            IntegrationsSettings::apply();
        } catch (\Throwable) {
        }

        if ($this->app->environment('production')) {
            $https = str_starts_with(strtolower((string) config('app.url')), 'https://');

            $secureEnv = env('SESSION_SECURE_COOKIE');
            $secure = null;
            if ($secureEnv === true || $secureEnv === 'true' || $secureEnv === '1') {
                $secure = true;
            } elseif ($secureEnv === false || $secureEnv === 'false' || $secureEnv === '0') {
                $secure = false;
            }

            config([
                'session.http_only' => true,
                'session.same_site' => env('SESSION_SAME_SITE', 'lax'),
                'session.secure' => $secure,
                'session.lifetime' => (int) env('SESSION_LIFETIME', 480),
            ]);

            if ($https) {
                URL::forceScheme('https');
            }
        }

        $uploadsRoot = base_path('uploads');
        if (! is_dir($uploadsRoot)) {
            @mkdir($uploadsRoot, 0775, true);
        }
        foreach (['logos', 'collections', 'gallery', 'general', 'branding'] as $dir) {
            $path = $uploadsRoot.'/'.$dir;
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }
    }
}
