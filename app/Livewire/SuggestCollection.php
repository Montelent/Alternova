<?php

namespace App\Livewire;

use App\Models\CollectionSubmission;
use App\Models\OpenSourceAlternative;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class SuggestCollection extends Component
{
    public string $submitter_name = '';

    public string $submitter_email = '';

    public string $title = '';

    public string $description = '';

    public string $intro = '';

    public string $alternative_slugs_text = '';

    public string $notes = '';

    public string $website = ''; // honeypot

    public bool $submitted = false;

    public ?string $trackingUrl = null;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->submitter_name = (string) (Auth::user()->name ?? '');
            $this->submitter_email = (string) (Auth::user()->email ?? '');
        }
    }

    protected function rules(): array
    {
        return [
            'submitter_name' => 'nullable|string|max:120',
            'submitter_email' => 'nullable|email|max:180',
            'title' => 'required|string|min:4|max:160',
            'description' => 'nullable|string|max:500',
            'intro' => 'nullable|string|max:3000',
            'alternative_slugs_text' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'website' => 'max:0',
        ];
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        if (! Schema::hasTable('collection_submissions')) {
            $this->addError('title', 'Submissions not ready. Run migrations first.');

            return;
        }

        $key = 'suggest-collection:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('title', 'Too many submissions. Try again later.');

            return;
        }

        $this->validate();

        $slugs = collect(preg_split('/[\s,]+/', $this->alternative_slugs_text) ?: [])
            ->map(fn ($s) => Str::slug(trim($s)))
            ->filter()
            ->unique()
            ->values()
            ->take(30)
            ->all();

        if (count($slugs) < 2) {
            $this->addError('alternative_slugs_text', 'List at least 2 alternative slugs (e.g. outline appflowy).');

            return;
        }

        RateLimiter::hit($key, 3600);

        $token = Str::random(40);

        $submission = CollectionSubmission::create([
            'submitter_name' => $this->submitter_name ?: null,
            'submitter_email' => $this->submitter_email ?: null,
            'title' => trim($this->title),
            'proposed_slug' => Str::slug($this->title),
            'description' => $this->description ?: null,
            'intro' => $this->intro ?: null,
            'alternative_slugs' => $slugs,
            'notes' => $this->notes ?: null,
            'status' => 'pending',
            'tracking_token' => $token,
            'ip_address' => request()->ip(),
        ]);

        $this->notifyAdmins($submission);

        $this->trackingUrl = route('collection-submissions.status', $token);
        $this->submitted = true;
        $this->reset(['title', 'description', 'intro', 'alternative_slugs_text', 'notes', 'website']);
    }

    protected function notifyAdmins(CollectionSubmission $submission): void
    {
        try {
            if (! SiteSetting::getBool('mail_alert_submission', true)) {
                return;
            }
            $adminEmail = SiteSetting::get('mail_admin_email')
                ?: User::query()->where('role', 'admin')->value('email');
            if (! $adminEmail || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $url = url('/admin/collection-submissions');
            $title = e($submission->title);
            $from = e($submission->submitter_email ?: 'anonymous');

            Mail::html(
                "<p>New collection suggestion: <strong>{$title}</strong> from {$from}.</p><p><a href=\"{$url}\">Review in admin</a></p>",
                function ($message) use ($adminEmail, $title) {
                    $message->to($adminEmail)->subject('Collection suggestion: '.$title);
                }
            );
        } catch (\Throwable) {
        }
    }

    public function render()
    {
        $examples = OpenSourceAlternative::query()
            ->where('is_published', true)
            ->orderBy('name')
            ->limit(12)
            ->pluck('slug');

        return view('livewire.suggest-collection', [
            'examples' => $examples,
        ])->layout('layouts.app', [
            'title' => 'Suggest a collection | Alternova',
            'description' => 'Propose a curated list of open-source alternatives for the Alternova directory.',
        ]);
    }
}
