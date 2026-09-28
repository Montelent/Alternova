<?php

namespace App\Livewire;

use App\Models\IssueReport;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ReportIssue extends Component
{
    public ?int $alternativeId = null;

    public string $type = 'wrong_info';

    public string $email = '';

    public string $message = '';

    public string $statusMessage = '';

    public bool $sent = false;

    public bool $open = false;

    public function openForm(): void
    {
        $this->open = true;
    }

    public function closeForm(): void
    {
        $this->open = false;
    }

    public function submit(): void
    {
        $this->validate([
            'type' => 'required|in:broken_link,wrong_info,spam,other',
            'message' => 'required|string|min:10|max:2000',
            'email' => 'nullable|email|max:190',
        ]);

        try {
            if (! Schema::hasTable('issue_reports')) {
                $this->statusMessage = 'Reporting is not ready yet. Please run database migrations.';

                return;
            }
        } catch (\Throwable) {
            $this->statusMessage = 'Reporting is temporarily unavailable.';

            return;
        }

        $key = 'report:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            $this->statusMessage = 'Too many reports from this network. Try later.';

            return;
        }
        RateLimiter::hit($key, 3600);

        IssueReport::create([
            'open_source_alternative_id' => $this->alternativeId,
            'type' => $this->type,
            'email' => $this->email ?: null,
            'page_url' => url()->previous() ?: request()->header('Referer'),
            'message' => $this->message,
            'status' => 'open',
            'ip_address' => request()->ip(),
        ]);

        $this->sent = true;
        $this->statusMessage = 'Thanks — we received your report.';
        $this->message = '';
        $this->email = '';
    }

    public function render()
    {
        return view('livewire.report-issue');
    }
}
