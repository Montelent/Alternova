<?php

namespace App\Livewire;

use App\Models\AlternativeSubmission;
use Livewire\Component;

class SuggestAlternative extends Component
{
    public string $submitter_name = '';

    public string $submitter_email = '';

    public string $proprietary_name = '';

    public string $alternative_name = '';

    public string $repo_url = '';

    public string $website_url = '';

    public string $description = '';

    public string $license_type = '';

    public string $website = ''; // honeypot

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'submitter_name' => 'nullable|string|max:120',
            'submitter_email' => 'nullable|email|max:180',
            'proprietary_name' => 'required|string|max:160',
            'alternative_name' => 'required|string|max:160',
            'repo_url' => 'required|url|max:500',
            'website_url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:2000',
            'license_type' => 'nullable|string|max:80',
            'website' => 'max:0',
        ];
    }

    public function submit(): void
    {
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $data = $this->validate();
        unset($data['website']);

        AlternativeSubmission::create([
            ...$data,
            'status' => 'pending',
            'ip_address' => request()->ip(),
        ]);

        $this->reset([
            'submitter_name',
            'submitter_email',
            'proprietary_name',
            'alternative_name',
            'repo_url',
            'website_url',
            'description',
            'license_type',
            'website',
        ]);

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.suggest-alternative')
            ->layout('layouts.app', [
                'title' => 'Suggest an open-source alternative | Alternova',
                'description' => 'Submit an open-source alternative for review. Help grow the Alternova directory.',
            ]);
    }
}
