<?php

namespace Tests\Feature;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Models\RepoMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubMetricsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_github_metrics_job_persists_repository_data_and_updates_the_alternative(): void
    {
        $tool = ProprietaryTool::factory()->create([
            'description' => null,
        ]);

        $alternative = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'description' => null,
            'repo_url' => 'https://github.com/example/project',
            'license_type' => null,
            'primary_language' => null,
        ]);

        Http::preventStrayRequests();
        Http::fake([
            'https://api.github.com/repos/example/project/languages' => Http::response([
                'PHP' => 900,
                'JavaScript' => 250,
            ], 200),
            'https://api.github.com/repos/example/project' => Http::response([
                'stargazers_count' => 1200,
                'forks_count' => 180,
                'open_issues_count' => 12,
                'pushed_at' => now()->subDay()->toIso8601String(),
                'license' => ['spdx_id' => 'MIT'],
                'default_branch' => 'main',
                'language' => 'PHP',
            ], 200),
        ]);

        SyncGitHubMetricsJob::dispatchSync($alternative);

        $metric = RepoMetric::query()
            ->where('open_source_alternative_id', $alternative->id)
            ->firstOrFail();

        $this->assertSame(1200, $metric->github_stars);
        $this->assertSame(180, $metric->github_forks);
        $this->assertSame(12, $metric->open_issues);
        $this->assertSame('MIT', $metric->verified_license);
        $this->assertSame(['PHP' => 900, 'JavaScript' => 250], $metric->languages);
        $this->assertNotNull($metric->synced_at);

        $alternative->refresh();
        $this->assertSame('PHP', $alternative->primary_language);
        $this->assertSame('MIT', $alternative->license_type);
        $this->assertGreaterThan(0, $alternative->overall_health_score);

        Http::assertSentCount(2);
    }

    public function test_invalid_github_repository_url_does_not_make_external_requests(): void
    {
        $tool = ProprietaryTool::factory()->create([
            'description' => null,
        ]);

        $alternative = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'description' => null,
            'repo_url' => 'https://example.com/not-a-github-repository',
        ]);

        Http::preventStrayRequests();

        SyncGitHubMetricsJob::dispatchSync($alternative);

        $this->assertDatabaseMissing('repo_metrics', [
            'open_source_alternative_id' => $alternative->id,
        ]);
        Http::assertNothingSent();
    }
}
