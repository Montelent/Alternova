<?php

namespace App\Mail;

use App\Models\OpenSourceAlternative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HealthDropAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public OpenSourceAlternative $alternative,
        public float $previousScore,
        public float $newScore,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Health score dropped — '.$this->alternative->name,
        );
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $url = e(url('/alternatives/'.$this->alternative->slug));
        $name = e($this->alternative->name);
        $user = e($this->userName);
        $prev = number_format($this->previousScore, 1);
        $new = number_format($this->newScore, 1);
        $drop = number_format($this->previousScore - $this->newScore, 1);
        $brand = e(config('app.name', 'Alternova'));
        $account = e(url('/account'));

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:system-ui,sans-serif;color:#0f172a">
  <table width="560" style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden">
    <tr><td style="background:#4f46e5;padding:18px 24px;color:#fff;font-weight:700">{$brand}</td></tr>
    <tr><td style="padding:28px 24px">
      <p style="margin:0 0 8px;font-size:13px;color:#b45309;font-weight:600">Watchlist alert</p>
      <h1 style="margin:0 0 12px;font-size:20px">{$name} health dropped</h1>
      <p style="margin:0 0 16px;color:#475569;font-size:14px;line-height:1.5">Hi {$user}, a tool on your watchlist lost <strong>{$drop}</strong> health points after the latest GitHub metrics sync.</p>
      <div style="display:flex;gap:16px;margin-bottom:20px">
        <div style="flex:1;padding:14px;background:#f8fafc;border-radius:12px;text-align:center">
          <div style="font-size:11px;color:#64748b;text-transform:uppercase">Before</div>
          <div style="font-size:22px;font-weight:700">{$prev}</div>
        </div>
        <div style="flex:1;padding:14px;background:#fef2f2;border-radius:12px;text-align:center">
          <div style="font-size:11px;color:#64748b;text-transform:uppercase">Now</div>
          <div style="font-size:22px;font-weight:700;color:#b91c1c">{$new}</div>
        </div>
      </div>
      <a href="{$url}" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;font-weight:600;font-size:14px;padding:12px 18px;border-radius:10px">View alternative</a>
      <p style="margin:20px 0 0;font-size:12px;color:#94a3b8">Manage watches in your <a href="{$account}" style="color:#64748b">account</a>.</p>
    </td></tr>
  </table>
</body></html>
HTML;
    }
}
