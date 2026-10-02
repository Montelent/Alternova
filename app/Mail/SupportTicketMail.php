<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $ticket,
        public string $event,
        public string $messageBody,
        public string $actorLabel = 'Support',
    ) {}

    public function envelope(): Envelope
    {
        $id = $this->ticket->public_id ?: '#'.$this->ticket->id;
        $subject = match ($this->event) {
            'created' => "[{$id}] We received your message",
            'staff_reply' => "[{$id}] New reply from support",
            'user_reply' => "[{$id}] User replied to ticket",
            'closed' => "[{$id}] Ticket closed",
            default => "[{$id}] Support ticket update",
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $app = e(config('app.name', 'Alternova'));
        $id = e($this->ticket->public_id ?: '#'.$this->ticket->id);
        $subj = e($this->ticket->subject ?: 'Support request');
        $url = e($this->ticket->publicUrl());
        $body = nl2br(e($this->messageBody));
        $actor = e($this->actorLabel);
        $name = e($this->ticket->name);

        $intro = match ($this->event) {
            'created' => "Hi {$name}, we received your support request.",
            'staff_reply' => "Hi {$name}, support replied to your ticket.",
            'user_reply' => "A user replied on ticket {$id}.",
            'closed' => "Ticket {$id} was closed.",
            default => "There is an update on your ticket.",
        };

        return <<<HTML
        <div style="font-family:system-ui,sans-serif;max-width:560px;margin:0 auto;line-height:1.5;color:#0f172a">
            <p style="font-size:14px;color:#64748b">{$app} Support</p>
            <h1 style="font-size:20px;margin:0 0 12px">{$intro}</h1>
            <p style="margin:0 0 8px"><strong>Ticket:</strong> {$id}<br><strong>Subject:</strong> {$subj}</p>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin:16px 0">
                <p style="margin:0 0 6px;font-size:12px;color:#64748b">From {$actor}</p>
                <div>{$body}</div>
            </div>
            <p><a href="{$url}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:600">View ticket</a></p>
            <p style="font-size:12px;color:#94a3b8">You can reply on the ticket page after signing in with the same email.</p>
        </div>
        HTML;
    }
}
