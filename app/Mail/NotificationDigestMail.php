<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class NotificationDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\UserNotification>  $notifications
     */
    public function __construct(
        public string $userName,
        public Collection $notifications,
        public int $unreadTotal,
    ) {}

    public function envelope(): Envelope
    {
        $n = $this->notifications->count();

        return new Envelope(
            subject: $n === 1
                ? 'You have 1 unread notification on Alternova'
                : "You have {$n} unread notifications on Alternova",
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $brand = e(config('app.name', 'Alternova'));
        $user = e($this->userName);
        $inbox = e(url('/notifications'));
        $account = e(url('/account'));
        $count = $this->notifications->count();
        $total = $this->unreadTotal;

        $rows = '';
        foreach ($this->notifications->take(15) as $n) {
            $title = e($n->title);
            $body = e(str($n->body ?? '')->limit(140));
            $type = e(str_replace('_', ' ', $n->type));
            $when = e(optional($n->created_at)?->diffForHumans() ?? '');
            $link = $n->url ? e($n->url) : $inbox;
            $rows .= <<<HTML
<tr>
  <td style="padding:14px 0;border-bottom:1px solid #e2e8f0">
    <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px">{$type} · {$when}</div>
    <a href="{$link}" style="color:#0f172a;font-weight:600;text-decoration:none;font-size:15px">{$title}</a>
    <div style="color:#475569;font-size:13px;margin-top:4px;line-height:1.4">{$body}</div>
  </td>
</tr>
HTML;
        }

        $more = $total > $count
            ? '<p style="margin:16px 0 0;font-size:13px;color:#64748b">And '.($total - $count).' more unread in your inbox.</p>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:system-ui,sans-serif;color:#0f172a">
  <table width="560" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden">
    <tr><td style="background:#4f46e5;padding:18px 24px;color:#fff;font-weight:700">{$brand}</td></tr>
    <tr><td style="padding:28px 24px">
      <p style="margin:0 0 8px;font-size:13px;color:#4f46e5;font-weight:600">Notification digest</p>
      <h1 style="margin:0 0 12px;font-size:20px">Hi {$user}, you have unread updates</h1>
      <p style="margin:0 0 20px;color:#475569;font-size:14px;line-height:1.5">Here's a summary of activity since your last visit. Open the inbox to mark items read.</p>
      <table width="100%" cellpadding="0" cellspacing="0">{$rows}</table>
      {$more}
      <p style="margin:24px 0 0">
        <a href="{$inbox}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 18px;border-radius:10px">Open notifications</a>
      </p>
      <p style="margin:20px 0 0;font-size:12px;color:#94a3b8">Manage account at <a href="{$account}" style="color:#64748b">{$account}</a></p>
    </td></tr>
  </table>
</body></html>
HTML;
    }
}
