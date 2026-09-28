<?php

namespace App\Observers;

use App\Mail\SubmissionAlert;
use App\Models\AlternativeSubmission;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AlternativeSubmissionObserver
{
    public function created(AlternativeSubmission $submission): void
    {
        try {
            MailSettings::apply();

            if (! MailSettings::alertsEnabled('submission')) {
                return;
            }

            $to = MailSettings::adminEmail();
            if (! $to) {
                return;
            }

            Mail::to($to)->send(new SubmissionAlert($submission));
        } catch (\Throwable $e) {
            Log::warning('Submission alert email failed: '.$e->getMessage());
        }
    }
}
