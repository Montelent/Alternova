<?php

namespace App\Livewire;

use App\Models\OpenSourceAlternative;
use Livewire\Attributes\Locked;
use Livewire\Component;

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
        $alt = $this->alternative;
        $prop = $alt->proprietaryTool;
        $propName = $prop?->name ?? 'proprietary tools';

        $pageTitle = $alt->name.' — Open-Source '.$propName.' Alternative | Alternova';
        $metaDescription = str(
            $alt->name.' is a free, self-hostable open-source alternative to '.$propName.'. '
            .($alt->description ?? '')
            .' License: '.($alt->license_type ?? 'OSS').'. Health score: '
            .number_format((float) $alt->overall_health_score, 1).'/100.'
        )->limit(155)->toString();

        $canonical = route('alternatives.show', $alt);

        $schemas = $this->buildSchemas($alt, $prop, $canonical);

        return view('livewire.alternative-detail', [
            'schemas' => $schemas,
            'proprietary' => $prop,
            'metric' => $alt->repoMetric,
            'heading' => $alt->name,
            'subheading' => 'The open-source alternative to '.$propName,
            'propName' => $propName,
        ])->layout('layouts.app', [
            'title' => $pageTitle,
            'description' => $metaDescription,
            'canonical' => $canonical,
            'ogType' => 'article',
            'ogImage' => $prop?->logo_path ? url($prop->logo_path) : null,
        ]);
    }

    protected function buildSchemas(OpenSourceAlternative $alt, $prop, string $canonical): array
    {
        $software = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $alt->name,
            'description' => $alt->description,
            'url' => $canonical,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Cross-platform',
            'softwareLicense' => $alt->license_type,
            'codeRepository' => $alt->repo_url,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ];

        if ($alt->website_url) {
            $software['sameAs'] = array_values(array_filter([$alt->website_url, $alt->repo_url]));
        }

        if ($alt->repoMetric && $alt->repoMetric->github_stars > 0) {
            $software['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => max(1, min(5, round($alt->overall_health_score / 20, 1))),
                'ratingCount' => max(1, (int) $alt->repoMetric->github_stars),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
        }

        $breadcrumb = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Open Source Alternatives',
                    'item' => route('finder'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $alt->name,
                    'item' => $canonical,
                ],
            ],
        ];

        $faq = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Is '.$alt->name.' free?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. '.$alt->name.' is released under the '.($alt->license_type ?? 'open-source').' license and can be self-hosted at no software cost.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'How difficult is it to self-host '.$alt->name.'?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Self-host difficulty is rated '.$alt->self_host_difficulty.'/5. Basic Docker knowledge is recommended for production setups.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'What is '.$alt->name.' an alternative to?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $alt->name.' is a self-hostable open-source alternative to '.($prop?->name ?? 'proprietary software').'.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Where can I contribute to '.$alt->name.'?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Visit the GitHub repository at '.$alt->repo_url.' to open issues or submit pull requests.',
                    ],
                ],
            ],
        ];

        return [$software, $breadcrumb, $faq];
    }
}
