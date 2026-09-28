<?php

namespace App\Observers;

use App\Mail\ContactMessageAlert;
use App\Models\ContactMessage;
use App\Support\MailSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactMessageObserver
{
    public function created(ContactMessage $message): void
    {
        try {
            MailSettings::apply();

            if (! MailSettings::alertsEnabled('contact')) {
                return;
            }

            $to = MailSettings::adminEmail();
            if (! $to) {
                return;
            }

            Mail::to($to)->send(new ContactMessageAlert($message));
        } catch (\Throwable $e) {
            Log::warning('Contact alert email failed: '.$e->getMessage());
        }
    }
}
