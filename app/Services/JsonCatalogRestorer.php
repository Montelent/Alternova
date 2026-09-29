<?php

namespace App\Services;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Models\SiteSetting;
use App\Models\SlugRedirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class JsonCatalogRestorer
{
    /**
     * @return array{imported_tools: int, imported_alternatives: int, imported_settings: int, imported_redirects: int, message: string}
     */
    public function restore(array $payload, bool $overwriteSettings = false): array
    {
        $tools = 0;
        $alts = 0;
        $settings = 0;
        $redirects = 0;

        if ($overwriteSettings && ! empty($payload['settings']) && is_array($payload['settings'])) {
            foreach ($payload['settings'] as $key => $value) {
                // Never restore secrets / mail passwords blindly if empty in backup is ok; skip empty keys
                if ($key === '' || $key === null) {
                    continue;
                }
                SiteSetting::set((string) $key, $value);
                $settings++;
            }
        }

        $toolIdMap = []; // old id => new id

        foreach ($payload['proprietary_tools'] ?? [] as $row) {
            if (! is_array($row) || empty($row['name'])) {
                continue;
            }
            $slug = $row['slug'] ?? Str::slug($row['name']);
            $tool = ProprietaryTool::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $row['name'],
                    'website_url' => $row['website_url'] ?? null,
                    'logo_path' => $row['logo_path'] ?? null,
                    'description' => $row['description'] ?? null,
                    'key_features' => $row['key_features'] ?? null,
                    'target_audience' => $row['target_audience'] ?? null,
                    'is_published' => (bool) ($row['is_published'] ?? true),
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                    'focus_keyword' => $row['focus_keyword'] ?? null,
                    'robots_meta' => $row['robots_meta'] ?? null,
                    'canonical_url' => $row['canonical_url'] ?? null,
                    'og_title' => $row['og_title'] ?? null,
                    'og_description' => $row['og_description'] ?? null,
                    'og_image_url' => $row['og_image_url'] ?? null,
                ]
            );
            if ($tool->trashed()) {
                $tool->restore();
            }
            if (isset($row['id'])) {
                $toolIdMap[(int) $row['id']] = $tool->id;
            }
            $tools++;
        }

        foreach ($payload['open_source_alternatives'] ?? [] as $row) {
            if (! is_array($row) || empty($row['name']) || empty($row['slug'])) {
                continue;
            }

            $propId = null;
            if (! empty($row['proprietary_tool_id']) && isset($toolIdMap[(int) $row['proprietary_tool_id']])) {
                $propId = $toolIdMap[(int) $row['proprietary_tool_id']];
            } elseif (! empty($row['proprietary_tool_id'])) {
                $propId = ProprietaryTool::query()->find($row['proprietary_tool_id'])?->id;
            }

            if (! $propId) {
                // Skip orphan alternatives rather than invent tools
                continue;
            }

            $data = [
                'proprietary_tool_id' => $propId,
                'name' => $row['name'],
                'repo_url' => $row['repo_url'] ?? null,
                'website_url' => $row['website_url'] ?? null,
                'description' => $row['description'] ?? null,
                'license_type' => $row['license_type'] ?? null,
                'self_host_difficulty' => $row['self_host_difficulty'] ?? 3,
                'docker_compose_blueprint' => $row['docker_compose_blueprint'] ?? null,
                'primary_language' => $row['primary_language'] ?? null,
                'overall_health_score' => $row['overall_health_score'] ?? 0,
                'is_published' => (bool) ($row['is_published'] ?? false),
                'is_featured' => (bool) ($row['is_featured'] ?? false),
                'is_sponsored' => (bool) ($row['is_sponsored'] ?? false),
                'sponsored_until' => $row['sponsored_until'] ?? null,
                'sponsor_label' => $row['sponsor_label'] ?? null,
                'meta_title' => $row['meta_title'] ?? null,
                'meta_description' => $row['meta_description'] ?? null,
                'focus_keyword' => $row['focus_keyword'] ?? null,
                'robots_meta' => $row['robots_meta'] ?? null,
                'canonical_url' => $row['canonical_url'] ?? null,
                'og_title' => $row['og_title'] ?? null,
                'og_description' => $row['og_description'] ?? null,
                'og_image_url' => $row['og_image_url'] ?? null,
                'editor_note' => $row['editor_note'] ?? null,
                'changelog' => $row['changelog'] ?? null,
                'gallery_urls' => $row['gallery_urls'] ?? null,
                'pros' => $row['pros'] ?? null,
                'cons' => $row['cons'] ?? null,
            ];

            $alt = OpenSourceAlternative::withTrashed()->updateOrCreate(
                ['slug' => $row['slug']],
                $data
            );
            if ($alt->trashed()) {
                $alt->restore();
            }
            $alts++;
        }

        if (Schema::hasTable('slug_redirects')) {
            foreach ($payload['slug_redirects'] ?? [] as $row) {
                if (! is_array($row) || empty($row['old_slug']) || empty($row['new_slug'])) {
                    continue;
                }
                SlugRedirect::query()->updateOrCreate(
                    [
                        'old_slug' => $row['old_slug'],
                        'model_type' => $row['model_type'] ?? 'alternative',
                    ],
                    ['new_slug' => $row['new_slug']]
                );
                $redirects++;
            }
        }

        return [
            'imported_tools' => $tools,
            'imported_alternatives' => $alts,
            'imported_settings' => $settings,
            'imported_redirects' => $redirects,
            'message' => "Restored {$tools} tools, {$alts} alternatives, {$settings} settings, {$redirects} redirects.",
        ];
    }
}
