<?php

namespace App\Providers;

use App\Support\IntegrationsSettings;
use App\Support\MailSettings;
use App\View\Composers\CmsNavComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'welcome'], CmsNavComposer::class);

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        try {
            IntegrationsSettings::apply();
        } catch (\Throwable) {
        }

        $uploadsRoot = base_path('uploads');
        if (! is_dir($uploadsRoot)) {
            @mkdir($uploadsRoot, 0775, true);
        }
        foreach (['logos', 'collections', 'gallery', 'general'] as $dir) {
            $path = $uploadsRoot.'/'.$dir;
            if (! is_dir($path)) {
                @mkdir($path, 0775, true);
            }
        }
    }
}
