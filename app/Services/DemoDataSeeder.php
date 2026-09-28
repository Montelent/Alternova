<?php

namespace App\Services;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Str;

class DemoDataSeeder
{
    /**
     * Seed a starter set of proprietary tools + alternatives if the catalog is empty-ish.
     *
     * @return array{tools: int, alternatives: int, message: string}
     */
    public function seed(bool $force = false): array
    {
        if (! $force && OpenSourceAlternative::query()->count() >= 5) {
            return [
                'tools' => 0,
                'alternatives' => 0,
                'message' => 'Catalog already has alternatives. Enable force to add demo rows anyway.',
            ];
        }

        $catalog = $this->catalog();

        $toolsCreated = 0;
        $altsCreated = 0;

        foreach ($catalog as $item) {
            $tool = ProprietaryTool::query()->firstOrCreate(
                ['slug' => Str::slug($item['proprietary'])],
                [
                    'name' => $item['proprietary'],
                    'website_url' => $item['proprietary_url'] ?? null,
                    'description' => $item['proprietary_description'],
                    'key_features' => $item['features'] ?? [],
                    'target_audience' => $item['audience'] ?? 'Teams and professionals',
                    'is_published' => true,
                ]
            );

            if ($tool->wasRecentlyCreated) {
                $toolsCreated++;
            }

            foreach ($item['alternatives'] as $alt) {
                $slug = Str::slug($alt['name']);
                if (OpenSourceAlternative::withTrashed()->where('slug', $slug)->exists()) {
                    continue;
                }

                $record = OpenSourceAlternative::create([
                    'proprietary_tool_id' => $tool->id,
                    'name' => $alt['name'],
                    'slug' => $slug,
                    'repo_url' => $alt['repo'],
                    'website_url' => $alt['website'] ?? null,
                    'description' => $alt['description'],
                    'license_type' => $alt['license'] ?? 'MIT',
                    'self_host_difficulty' => $alt['difficulty'] ?? 3,
                    'primary_language' => $alt['language'] ?? null,
                    'overall_health_score' => 0,
                    'is_published' => true,
                    'is_featured' => $alt['featured'] ?? false,
                    'docker_compose_blueprint' => $alt['docker'] ?? null,
                ]);

                $altsCreated++;

                try {
                    SyncGitHubMetricsJob::dispatchSync($record);
                } catch (\Throwable) {
                    // metrics optional during seed
                }
            }
        }

        return [
            'tools' => $toolsCreated,
            'alternatives' => $altsCreated,
            'message' => "Created {$toolsCreated} proprietary tool(s) and {$altsCreated} alternative(s).",
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function catalog(): array
    {
        return [
            [
                'proprietary' => 'Notion',
                'proprietary_url' => 'https://www.notion.so',
                'proprietary_description' => 'All-in-one workspace for notes, docs, wikis, and project management.',
                'audience' => 'Teams and knowledge workers',
                'features' => ['Docs & wikis', 'Databases', 'Collaboration', 'Templates'],
                'alternatives' => [
                    [
                        'name' => 'AppFlowy',
                        'repo' => 'https://github.com/AppFlowy-IO/AppFlowy',
                        'website' => 'https://appflowy.io',
                        'description' => 'AppFlowy is an open-source Notion alternative focused on privacy and offline-first collaboration.',
                        'license' => 'AGPL-3.0',
                        'difficulty' => 3,
                        'language' => 'Dart',
                        'featured' => true,
                    ],
                    [
                        'name' => 'AFFiNE',
                        'repo' => 'https://github.com/toeverything/AFFiNE',
                        'website' => 'https://affine.pro',
                        'description' => 'AFFiNE combines docs, whiteboards, and databases in a local-first open-source workspace.',
                        'license' => 'MIT',
                        'difficulty' => 3,
                        'language' => 'TypeScript',
                        'featured' => true,
                    ],
                ],
            ],
            [
                'proprietary' => 'Slack',
                'proprietary_url' => 'https://slack.com',
                'proprietary_description' => 'Team messaging and collaboration platform.',
                'audience' => 'Organizations of all sizes',
                'features' => ['Channels', 'Direct messages', 'Integrations', 'Search'],
                'alternatives' => [
                    [
                        'name' => 'Mattermost',
                        'repo' => 'https://github.com/mattermost/mattermost',
                        'website' => 'https://mattermost.com',
                        'description' => 'Mattermost is a self-hostable Slack alternative for secure team messaging.',
                        'license' => 'AGPL-3.0',
                        'difficulty' => 3,
                        'language' => 'Go',
                        'featured' => true,
                    ],
                    [
                        'name' => 'Rocket.Chat',
                        'repo' => 'https://github.com/RocketChat/Rocket.Chat',
                        'website' => 'https://www.rocket.chat',
                        'description' => 'Rocket.Chat is an open-source communications platform with channels, omnichannel, and federation.',
                        'license' => 'MIT',
                        'difficulty' => 3,
                        'language' => 'TypeScript',
                        'featured' => false,
                    ],
                ],
            ],
            [
                'proprietary' => 'Google Analytics',
                'proprietary_url' => 'https://analytics.google.com',
                'proprietary_description' => 'Web analytics platform from Google.',
                'audience' => 'Marketers and product teams',
                'features' => ['Traffic reports', 'Events', 'Funnels', 'Audience segments'],
                'alternatives' => [
                    [
                        'name' => 'Plausible Analytics',
                        'repo' => 'https://github.com/plausible/analytics',
                        'website' => 'https://plausible.io',
                        'description' => 'Plausible is a lightweight, privacy-friendly, open-source web analytics alternative to Google Analytics.',
                        'license' => 'AGPL-3.0',
                        'difficulty' => 2,
                        'language' => 'Elixir',
                        'featured' => true,
                    ],
                    [
                        'name' => 'Umami',
                        'repo' => 'https://github.com/umami-software/umami',
                        'website' => 'https://umami.is',
                        'description' => 'Umami is a simple, fast, privacy-focused open-source analytics solution.',
                        'license' => 'MIT',
                        'difficulty' => 2,
                        'language' => 'TypeScript',
                        'featured' => false,
                    ],
                ],
            ],
        ];
    }
}
