<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use Livewire\Component;
use Livewire\Attributes\Locked;

class AlternativeDetail extends Component
{
    #[Locked]
    public OpenSourceAlternative $alternative;

    public function mount(OpenSourceAlternative $alternative): void
    {
        $this->alternative = $alternative->load(['proprietaryTool', 'repoMetric', 'tags']);

        abort_unless($this->alternative->is_published, 404);
    }

    public function render()
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $this->alternative->name,
            'description' => $this->alternative->description,
            'url' => $this->alternative->website_url ?? url()->current(),
            'applicationCategory' => 'DeveloperApplication',
            'operatingSystem' => 'Cross-platform',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
            'softwareVersion' => 'latest',
            'license' => $this->alternative->license_type,
            'codeRepository' => $this->alternative->repo_url,
        ];

        if ($this->alternative->repoMetric) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => min(5, round($this->alternative->overall_health_score / 20, 1)),
                'ratingCount' => $this->alternative->repoMetric->github_stars,
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        return view('livewire.alternative-detail', [
            'schema' => $schema,
            'proprietary' => $this->alternative->proprietaryTool,
            'metric' => $this->alternative->repoMetric,
        ])->layout('layouts.app', [
            'title' => $this->alternative->name . ' – Open Source Alternative',
            'description' => str($this->alternative->description)->limit(160),
            'ogImage' => $this->alternative->proprietaryTool?->logo_path,
        ]);
    }
}
