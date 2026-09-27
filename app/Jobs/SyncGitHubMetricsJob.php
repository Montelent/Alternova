<?php

namespace App\Jobs;

use App\Models\OpenSourceAlternative;
use App\Models\RepoMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncGitHubMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public OpenSourceAlternative $alternative
    ) {}

    public function handle(): void
    {
        $repoUrl = $this->alternative->repo_url;

        // Extract owner/repo from GitHub URL
        if (!preg_match('#github\.com/([^/]+)/([^/]+?)(?:\.git)?/?$#i', $repoUrl, $matches)) {
            Log::warning("Invalid GitHub URL for alternative {$this->alternative->id}: {$repoUrl}");
            return;
        }

        [$owner, $repo] = [$matches[1], $matches[2]];

        $token = config('services.github.token');

        $headers = [
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ];

        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        try {
            // Fetch repository data
            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->get("https://api.github.com/repos/{$owner}/{$repo}");

            if ($response->failed()) {
                Log::error("GitHub API failed for {$owner}/{$repo}", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return;
            }

            $data = $response->json();

            // Fetch languages
            $languagesResponse = Http::withHeaders($headers)
                ->timeout(10)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/languages");

            $languages = $languagesResponse->successful() ? $languagesResponse->json() : [];

            // Determine primary language
            $primaryLanguage = $data['language'] ?? (empty($languages) ? null : array_key_first($languages));

            // Last commit via commits endpoint (more reliable than pushed_at sometimes)
            $lastCommitAt = isset($data['pushed_at'])
                ? Carbon::parse($data['pushed_at'])
                : null;

            $metric = RepoMetric::updateOrCreate(
                ['open_source_alternative_id' => $this->alternative->id],
                [
                    'github_stars' => $data['stargazers_count'] ?? 0,
                    'github_forks' => $data['forks_count'] ?? 0,
                    'open_issues' => $data['open_issues_count'] ?? 0,
                    'last_commit_at' => $lastCommitAt,
                    'verified_license' => $data['license']['spdx_id'] ?? null,
                    'default_branch' => $data['default_branch'] ?? 'main',
                    'languages' => $languages,
                    'synced_at' => now(),
                ]
            );

            // Update primary language on the alternative
            $this->alternative->update([
                'primary_language' => $primaryLanguage,
                'license_type' => $metric->verified_license ?? $this->alternative->license_type,
            ]);

            // Recalculate health score
            $this->alternative->recalculateHealthScore();

            Log::info("Synced metrics for {$owner}/{$repo}", [
                'stars' => $metric->github_stars,
                'score' => $this->alternative->fresh()->overall_health_score,
            ]);
        } catch (\Throwable $e) {
            Log::error("Exception syncing GitHub metrics for {$owner}/{$repo}: " . $e->getMessage());
            throw $e;
        }
    }
}
