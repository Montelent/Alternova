<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Safe sample content for new installs / script buyers.
 * Never deletes existing rows; only creates missing slugs.
 */
class DemoCatalogSeeder
{
    /**
     * @return array{tools_created: int, alternatives_created: int, linked: int, skipped: int}
     */
    public function seed(): array
    {
        $toolsCreated = 0;
        $altsCreated = 0;
        $linked = 0;
        $skipped = 0;

        foreach ($this->tools() as $row) {
            $existing = ProprietaryTool::query()->where('slug', $row['slug'])->first();
            if ($existing) {
                $skipped++;
                $tool = $existing;
            } else {
                $tool = ProprietaryTool::query()->create([
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'website_url' => $row['website_url'],
                    'description' => $row['description'],
                    'key_features' => $row['key_features'],
                    'target_audience' => $row['target_audience'],
                    'is_published' => true,
                ]);
                $toolsCreated++;
            }

            foreach ($row['alternatives'] as $altRow) {
                $alt = OpenSourceAlternative::query()->where('slug', $altRow['slug'])->first();
                if ($alt) {
                    $skipped++;
                } else {
                    $alt = OpenSourceAlternative::query()->create([
                        'proprietary_tool_id' => $tool->id,
                        'name' => $altRow['name'],
                        'slug' => $altRow['slug'],
                        'repo_url' => $altRow['repo_url'],
                        'website_url' => $altRow['website_url'] ?? null,
                        'description' => $altRow['description'],
                        'license_type' => $altRow['license_type'],
                        'self_host_difficulty' => $altRow['self_host_difficulty'],
                        'primary_language' => $altRow['primary_language'],
                        'overall_health_score' => $altRow['overall_health_score'] ?? 70,
                        'is_published' => true,
                        'is_featured' => $altRow['is_featured'] ?? false,
                    ]);
                    $altsCreated++;
                }

                try {
                    if (Schema::hasTable('alternative_proprietary_tool') && method_exists($alt, 'proprietaryTools')) {
                        $alt->proprietaryTools()->syncWithoutDetaching([$tool->id]);
                        $linked++;
                    }
                } catch (\Throwable) {
                }
            }
        }

        return [
            'tools_created' => $toolsCreated,
            'alternatives_created' => $altsCreated,
            'linked' => $linked,
            'skipped' => $skipped,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function tools(): array
    {
        return [
            [
                'name' => 'Notion',
                'slug' => 'notion',
                'website_url' => 'https://www.notion.so',
                'description' => 'All-in-one workspace for notes, docs, wikis, and light project tracking used by teams of every size.',
                'key_features' => ['Docs', 'Databases', 'Wikis', 'Collaboration'],
                'target_audience' => 'Teams and knowledge workers',
                'alternatives' => [
                    [
                        'name' => 'AppFlowy',
                        'slug' => 'appflowy',
                        'repo_url' => 'https://github.com/AppFlowy-IO/AppFlowy',
                        'website_url' => 'https://appflowy.io',
                        'description' => 'Open-source alternative to Notion with offline-first design and self-host options. Good starting point for teams that want ownership of their knowledge base.',
                        'license_type' => 'AGPL-3.0',
                        'self_host_difficulty' => 3,
                        'primary_language' => 'Rust',
                        'overall_health_score' => 82,
                        'is_featured' => true,
                    ],
                    [
                        'name' => 'Outline',
                        'slug' => 'outline',
                        'repo_url' => 'https://github.com/outline/outline',
                        'website_url' => 'https://www.getoutline.com',
                        'description' => 'Team knowledge base with Markdown, real-time editing, and clean permissions. Popular self-hosted wiki for product and engineering orgs.',
                        'license_type' => 'BSL-1.1',
                        'self_host_difficulty' => 3,
                        'primary_language' => 'TypeScript',
                        'overall_health_score' => 85,
                        'is_featured' => true,
                    ],
                ],
            ],
            [
                'name' => 'Slack',
                'slug' => 'slack',
                'website_url' => 'https://slack.com',
                'description' => 'Team chat and collaboration hub with channels, threads, and extensive integrations.',
                'key_features' => ['Channels', 'Integrations', 'Huddles', 'Search'],
                'target_audience' => 'Remote and hybrid teams',
                'alternatives' => [
                    [
                        'name' => 'Rocket.Chat',
                        'slug' => 'rocket-chat',
                        'repo_url' => 'https://github.com/RocketChat/Rocket.Chat',
                        'website_url' => 'https://rocket.chat',
                        'description' => 'Self-hostable team chat with channels, omnichannel features, and marketplace apps. Strong fit when data residency matters.',
                        'license_type' => 'MIT',
                        'self_host_difficulty' => 4,
                        'primary_language' => 'TypeScript',
                        'overall_health_score' => 78,
                        'is_featured' => false,
                    ],
                    [
                        'name' => 'Mattermost',
                        'slug' => 'mattermost',
                        'repo_url' => 'https://github.com/mattermost/mattermost',
                        'website_url' => 'https://mattermost.com',
                        'description' => 'Secure collaboration platform for technical teams. Slack-like UX with on-prem deployment and strong admin controls.',
                        'license_type' => 'AGPL-3.0',
                        'self_host_difficulty' => 3,
                        'primary_language' => 'Go',
                        'overall_health_score' => 80,
                        'is_featured' => true,
                    ],
                ],
            ],
            [
                'name' => 'Figma',
                'slug' => 'figma',
                'website_url' => 'https://www.figma.com',
                'description' => 'Browser-based design and prototyping tool used for UI, design systems, and collaborative design reviews.',
                'key_features' => ['Vector design', 'Prototyping', 'Components', 'Comments'],
                'target_audience' => 'Designers and product teams',
                'alternatives' => [
                    [
                        'name' => 'Penpot',
                        'slug' => 'penpot',
                        'repo_url' => 'https://github.com/penpot/penpot',
                        'website_url' => 'https://penpot.app',
                        'description' => 'Open-source design and prototyping platform. Self-hostable alternative for teams that want design files under their control.',
                        'license_type' => 'MPL-2.0',
                        'self_host_difficulty' => 3,
                        'primary_language' => 'Clojure',
                        'overall_health_score' => 76,
                        'is_featured' => true,
                    ],
                ],
            ],
        ];
    }
}
