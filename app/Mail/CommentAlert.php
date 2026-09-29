<?php

namespace App\Mail;

use App\Models\AlternativeComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AlternativeComment $comment) {}

    public function envelope(): Envelope
    {
        $alt = $this->comment->alternative?->name ?? 'an alternative';

        return new Envelope(
            subject: '[Alternova] New comment on '.$alt,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    protected function buildHtml(): string
    {
        $c = $this->comment;
        $alt = $c->alternative;
        $url = $alt ? route('alternatives.show', $alt) : url('/');
        $admin = url('/admin');
        $status = $c->is_approved ? 'Published' : 'Pending approval';
        $body = e(str($c->body)->limit(500));
        $name = e($c->author_name);

        return <<<HTML
        <div style="font-family:system-ui,sans-serif;max-width:560px;margin:0 auto;color:#0f172a">
            <h2 style="margin:0 0 12px">New comment</h2>
            <p style="margin:0 0 8px"><strong>{$name}</strong> on <a href="{$url}">{$alt?->name}</a></p>
            <p style="margin:0 0 8px;color:#64748b">Status: {$status}</p>
            <blockquote style="margin:16px 0;padding:12px 16px;background:#f8fafc;border-left:4px solid #4f46e5">{$body}</blockquote>
            <p><a href="{$admin}">Open admin</a></p>
        </div>
        HTML;
    }
}
