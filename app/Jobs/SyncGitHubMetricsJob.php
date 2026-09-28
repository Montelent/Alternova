<?php

namespace App\Jobs;

use App\Models\OpenSourceAlternative;
use App\Models\RepoMetric;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        $this->alternative->refresh();

        $repoUrl = trim((string) $this->alternative->repo_url);

        if (! preg_match('~github\.com[/:]([^/\s]+)/([^/\s\.?#]+)~i', $repoUrl, $matches)) {
            Log::warning("Invalid GitHub URL for alternative {$this->alternative->id}: {$repoUrl}");

            return;
        }

        $owner = $matches[1];
        $repo = rtrim($matches[2], '/');
        $repo = preg_replace('/\.git$/i', '', $repo);

        $token = config('services.github.token') ?: env('GITHUB_TOKEN');

        $request = Http::timeout(20)
            ->connectTimeout(10)
            ->acceptJson()
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent' => 'Alternova-MetricsSync/1.0',
            ]);

        if ($token) {
            $request = $request->withToken($token);
        }

        try {
            $response = $request->get("https://api.github.com/repos/{$owner}/{$repo}");

            if ($response->failed()) {
                Log::error("GitHub API failed for {$owner}/{$repo}", [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);

                return;
            }

            $data = $response->json();

            $languages = [];
            try {
                $languagesResponse = $request->get("https://api.github.com/repos/{$owner}/{$repo}/languages");
                if ($languagesResponse->successful()) {
                    $languages = $languagesResponse->json() ?: [];
                }
            } catch (\Throwable) {
                // optional
            }

            $primaryLanguage = $data['language']
                ?? (is_array($languages) && $languages ? array_key_first($languages) : null);

            $lastCommitAt = isset($data['pushed_at'])
                ? Carbon::parse($data['pushed_at'])
                : null;

            $license = $data['license']['spdx_id'] ?? null;
            if ($license === 'NOASSERTION') {
                $license = null;
            }

            RepoMetric::updateOrCreate(
                ['open_source_alternative_id' => $this->alternative->id],
                [
                    'github_stars' => (int) ($data['stargazers_count'] ?? 0),
                    'github_forks' => (int) ($data['forks_count'] ?? 0),
                    'open_issues' => (int) ($data['open_issues_count'] ?? 0),
                    'last_commit_at' => $lastCommitAt,
                    'verified_license' => $license,
                    'default_branch' => $data['default_branch'] ?? 'main',
                    'languages' => $languages,
                    'synced_at' => now(),
                ]
            );

            $updates = [];
            if ($primaryLanguage) {
                $updates['primary_language'] = $primaryLanguage;
            }
            if ($license && ! $this->alternative->license_type) {
                $updates['license_type'] = $license;
            }
            if ($updates) {
                $this->alternative->forceFill($updates)->save();
            }

            $score = $this->alternative->recalculateHealthScore();

            Log::info("Synced metrics for {$owner}/{$repo}", [
                'stars' => $data['stargazers_count'] ?? 0,
                'score' => $score,
            ]);
        } catch (\Throwable $e) {
            Log::error("Exception syncing GitHub metrics for {$owner}/{$repo}: ".$e->getMessage());
            throw $e;
        }
    }
}
