<?php

namespace App\Providers;

use App\Models\AlternativeComment;
use App\Models\AlternativeSubmission;
use App\Models\ContactMessage;
use App\Models\OpenSourceAlternative;
use App\Observers\AlternativeCommentObserver;
use App\Observers\AlternativeSubmissionObserver;
use App\Observers\ContactMessageObserver;
use App\Observers\OpenSourceAlternativeObserver;
use App\Support\MailSettings;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->usePublicPath(base_path('public'));
    }

    public function boot(): void
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        try {
            MailSettings::apply();
        } catch (\Throwable) {
        }

        ContactMessage::observe(ContactMessageObserver::class);
        AlternativeSubmission::observe(AlternativeSubmissionObserver::class);
        OpenSourceAlternative::observe(OpenSourceAlternativeObserver::class);
        AlternativeComment::observe(AlternativeCommentObserver::class);
    }
}
