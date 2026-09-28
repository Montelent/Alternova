<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $note = '') {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Alternova test email',
        );
    }

    public function content(): Content
    {
        $body = '<!DOCTYPE html><html><body style="font-family:system-ui,sans-serif">'
            .'<h2>Mail is working</h2>'
            .'<p>This is a test message from <strong>Alternova</strong>.</p>'
            .'<p>Mailer: <code>'.e(config('mail.default')).'</code></p>'
            .($this->note ? '<p>'.e($this->note).'</p>' : '')
            .'<p style="color:#64748b;font-size:12px">'.e(now()->toDayDateTimeString()).'</p>'
            .'</body></html>';

        return new Content(htmlString: $body);
    }
}
