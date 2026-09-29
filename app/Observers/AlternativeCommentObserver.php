<?php

namespace App\Observers;

use App\Mail\CommentAlert;
use App\Models\AlternativeComment;
use App\Models\SiteSetting;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlternativeCommentObserver
{
    public function created(AlternativeComment $comment): void
    {
        try {
            MailSettings::apply();

            $enabled = SiteSetting::getBool('mail_alert_comment', true);
            if (! $enabled) {
                return;
            }

            $to = MailSettings::adminEmail();
            if (! $to) {
                return;
            }

            $comment->loadMissing('alternative');
            Mail::to($to)->send(new CommentAlert($comment));
        } catch (\Throwable $e) {
            Log::warning('Comment alert email failed: '.$e->getMessage());
        }
    }
}
