<?php

namespace App\Mail;

use App\Models\AlternativeComment;
use App\Models\OpenSourceAlternative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommentReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AlternativeComment $parent,
        public AlternativeComment $reply,
        public OpenSourceAlternative $alternative,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New reply on '.$this->alternative->name.' — Alternova',
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $url = e(url('/alternatives/'.$this->alternative->slug));
        $altName = e($this->alternative->name);
        $replyAuthor = e($this->reply->author_name);
        $body = e(str($this->reply->body)->limit(400));
        $parentSnippet = e(str($this->parent->body)->limit(120));
        $brand = e(config('app.name', 'Alternova'));

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:system-ui,sans-serif;color:#0f172a">
  <table width="560" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden">
    <tr><td style="background:#4f46e5;padding:18px 24px;color:#fff;font-weight:700">{$brand}</td></tr>
    <tr><td style="padding:28px 24px">
      <p style="margin:0 0 8px;font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;font-weight:600">Comment reply</p>
      <h1 style="margin:0 0 16px;font-size:20px">Someone replied on {$altName}</h1>
      <p style="margin:0 0 8px;font-size:13px;color:#64748b">Your comment:</p>
      <blockquote style="margin:0 0 16px;padding:12px 14px;background:#f8fafc;border-left:3px solid #cbd5e1;color:#475569;font-size:13px">{$parentSnippet}</blockquote>
      <p style="margin:0 0 8px;font-size:13px;color:#64748b"><strong style="color:#0f172a">{$replyAuthor}</strong> wrote:</p>
      <div style="padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;font-size:14px;line-height:1.5;margin-bottom:20px">{$body}</div>
      <a href="{$url}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 18px;border-radius:10px">View discussion</a>
    </td></tr>
  </table>
</body></html>
HTML;
    }
}
