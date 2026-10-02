<?php

namespace App\Services;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Support\GitHubUrl;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * After save/publish: fill empty descriptions, sync health metrics, check links.
 * Never overwrites a description the admin already wrote.
 */
class CatalogEnrichmentService
{
    protected static bool $running = false;

    /**
     * @param  array{repo_changed?: bool, website_changed?: bool, force_description?: bool}  $opts
     * @return array<string, mixed>
     */
    public function enrichAlternative(OpenSourceAlternative $alt, array $opts = []): array
    {
        if (static::$running) {
            return ['skipped' => true];
        }

        static::$running = true;
        $notes = [];

        $repoChanged = (bool) ($opts['repo_changed'] ?? false);
        $websiteChanged = (bool) ($opts['website_changed'] ?? false);
        $forceDescription = (bool) ($opts['force_description'] ?? false);

        try {
            $alt->refresh();

            // 1) Description from GitHub when empty
            if ($this->descriptionIsEmpty($alt->description) || $forceDescription) {
                if ($alt->repo_url && GitHubUrl::parse((string) $alt->repo_url)) {
                    $propName = $alt->proprietaryTool?->name
                        ?? $alt->proprietaryTools()->value('name');

                    $result = app(DescriptionGeneratorService::class)
                        ->fromGitHubRepo((string) $alt->repo_url, $propName);

                    if ($result['success'] && ! empty($result['description'])) {
                        $updates = ['description' => $result['description']];

                        if (empty($alt->primary_language) && ! empty($result['primary_language'])) {
                            $updates['primary_language'] = $result['primary_language'];
                        }
                        if (empty($alt->license_type) && ! empty($result['license_type'])) {
                            $updates['license_type'] = $result['license_type'];
                        }
                        if (empty($alt->website_url) && ! empty($result['website_url'])) {
                            $updates['website_url'] = $result['website_url'];
                        }

                        $alt->forceFill($updates)->saveQuietly();
                        $notes[] = 'description_from_github';
                    } else {
                        $notes[] = 'description_github_failed: '.($result['message'] ?? 'unknown');
                    }
                }
            }

            // 2) Health score / GitHub metrics when repo URL present
            if ($alt->repo_url && GitHubUrl::parse((string) $alt->repo_url)) {
                $hasMetric = $alt->repoMetric()->exists();
                $needsMetrics = $repoChanged
                    || ! $hasMetric
                    || $alt->overall_health_score === null
                    || (float) $alt->overall_health_score <= 0;

                if ($needsMetrics) {
                    try {
                        SyncGitHubMetricsJob::dispatchSync($alt->fresh());
                        $notes[] = 'metrics_synced';
                    } catch (\Throwable $e) {
                        Log::warning('Auto metrics sync failed: '.$e->getMessage());
                        $notes[] = 'metrics_failed: '.$e->getMessage();
                    }
                }
            }

            // 3) Link health when URLs exist and never checked, or URLs changed
            $needsLinkCheck = $alt->links_checked_at === null
                || $repoChanged
                || $websiteChanged;

            if ($needsLinkCheck && ($alt->repo_url || $alt->website_url)) {
                try {
                    app(LinkHealthService::class)->checkAlternative($alt->fresh());
                    $notes[] = 'links_checked';
                } catch (\Throwable $e) {
                    Log::warning('Auto link check failed: '.$e->getMessage());
                    $notes[] = 'links_failed: '.$e->getMessage();
                }
            }

            return ['ok' => true, 'notes' => $notes];
        } finally {
            static::$running = false;
        }
    }

    /**
     * @param  array{force_description?: bool}  $opts
     * @return array<string, mixed>
     */
    public function enrichProprietaryTool(ProprietaryTool $tool, array $opts = []): array
    {
        if (static::$running) {
            return ['skipped' => true];
        }

        static::$running = true;
        $notes = [];
        $forceDescription = (bool) ($opts['force_description'] ?? false);

        try {
            $tool->refresh();

            if (($this->descriptionIsEmpty($tool->description) || $forceDescription)
                && ! empty($tool->website_url)
            ) {
                $result = app(DescriptionGeneratorService::class)
                    ->fromWebsite((string) $tool->website_url, (string) $tool->name);

                if (! empty($result['description'])) {
                    $tool->forceFill(['description' => $result['description']])->saveQuietly();
                    $notes[] = $result['success'] ? 'description_from_website' : 'description_template';
                }
            }

            return ['ok' => true, 'notes' => $notes];
        } finally {
            static::$running = false;
        }
    }

    public function descriptionIsEmpty(?string $html): bool
    {
        $text = trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return $text === '' || Str::length($text) < 12;
    }
}
