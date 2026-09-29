<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\OpenSourceAlternative>  $newAlternatives
     * @param  Collection<int, \App\Models\OpenSourceAlternative>  $updatedAlternatives
     * @param  Collection<int, \App\Models\OpenSourceAlternative>  $featured
     */
    public function __construct(
        public Collection $newAlternatives,
        public Collection $updatedAlternatives,
        public string $unsubscribeUrl,
        public Collection $featured = new Collection,
        public int $days = 7,
    ) {
    }

    public function envelope(): Envelope
    {
        $count = $this->newAlternatives->count() + $this->updatedAlternatives->count();
        $subject = $count > 0
            ? 'Alternova weekly digest — '.$count.' open-source update'.($count === 1 ? '' : 's')
            : 'Alternova weekly digest — what\'s trending';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(htmlString: $this->htmlBody());
    }

    protected function htmlBody(): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $brand = e(config('app.name', 'Alternova'));

        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:system-ui,-apple-system,Segoe UI,sans-serif;color:#0f172a">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px">
    <tr><td align="center">
      <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0">
        <tr><td style="background:#4f46e5;padding:20px 28px">
          <div style="color:#e0e7ff;font-size:13px;font-weight:600;letter-spacing:.04em;text-transform:uppercase">{$brand}</div>
          <div style="color:#ffffff;font-size:22px;font-weight:700;margin-top:6px">Weekly open-source digest</div>
          <div style="color:#c7d2fe;font-size:13px;margin-top:6px">Last {$this->days} days · self-hostable tools worth knowing</div>
        </td></tr>
        <tr><td style="padding:28px">
HTML;

        if ($this->newAlternatives->isNotEmpty()) {
            $html .= '<div style="font-size:15px;font-weight:700;margin:0 0 12px;color:#0f172a">New alternatives</div>';
            foreach ($this->newAlternatives as $alt) {
                $html .= $this->card($alt);
            }
        }

        if ($this->updatedAlternatives->isNotEmpty()) {
            $html .= '<div style="font-size:15px;font-weight:700;margin:24px 0 12px;color:#0f172a">Updated listings</div>';
            foreach ($this->updatedAlternatives as $alt) {
                $html .= $this->card($alt, compact: true);
            }
        }

        if ($this->featured->isNotEmpty() && $this->newAlternatives->isEmpty() && $this->updatedAlternatives->isEmpty()) {
            $html .= '<p style="color:#475569;font-size:14px;line-height:1.5;margin:0 0 16px">Quiet week for brand-new listings — here are featured picks from the catalog.</p>';
            $html .= '<div style="font-size:15px;font-weight:700;margin:0 0 12px">Featured on Alternova</div>';
            foreach ($this->featured as $alt) {
                $html .= $this->card($alt);
            }
        }

        if ($this->newAlternatives->isEmpty() && $this->updatedAlternatives->isEmpty() && $this->featured->isEmpty()) {
            $html .= '<p style="color:#475569;font-size:14px">No published changes this period. Browse the <a href="'.e($base.'/alternatives').'" style="color:#4f46e5">full catalog</a>.</p>';
        }

        $html .= <<<HTML
          <div style="margin-top:28px;padding-top:20px;border-top:1px solid #e2e8f0;font-size:12px;color:#94a3b8;line-height:1.6">
            <a href="{$base}/whats-new" style="color:#4f46e5;text-decoration:none;font-weight:600">What's new</a>
            &nbsp;·&nbsp;
            <a href="{$base}/alternatives" style="color:#4f46e5;text-decoration:none;font-weight:600">Browse alternatives</a>
            &nbsp;·&nbsp;
            <a href="{$base}/domains" style="color:#4f46e5;text-decoration:none;font-weight:600">Domain ideas</a>
            <br><br>
            You receive this because you subscribed on Alternova.
            <a href="{$this->unsubscribeUrl}" style="color:#64748b">Unsubscribe</a>
          </div>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
HTML;

        return $html;
    }

    protected function card($alt, bool $compact = false): string
    {
        $url = e(url('/alternatives/'.$alt->slug));
        $name = e($alt->name);
        $prop = e($alt->proprietaryTool?->name ?? 'proprietary tools');
        $desc = e(str($alt->description ?? '')->limit($compact ? 90 : 140));
        $health = number_format((float) $alt->overall_health_score, 1);
        $license = e($alt->license_type ?? '');

        $meta = "Health {$health}/100";
        if ($license !== '') {
            $meta .= " · {$license}";
        }

        return <<<HTML
<div style="border:1px solid #e2e8f0;border-radius:12px;padding:14px 16px;margin:0 0 10px">
  <a href="{$url}" style="color:#0f172a;font-size:15px;font-weight:700;text-decoration:none">{$name}</a>
  <div style="color:#64748b;font-size:12px;margin-top:4px">Alternative to {$prop} · {$meta}</div>
  <div style="color:#475569;font-size:13px;margin-top:8px;line-height:1.45">{$desc}</div>
</div>
HTML;
    }
}
