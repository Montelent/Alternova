<?php

namespace Tests\Feature;

use App\Jobs\SyncGitHubMetricsJob;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Models\RepoMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QueueWorkerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_queue_worker_executes_github_metrics_job_and_persists_results(): void
    {
        config(['queue.default' => 'database']);

        $tool = ProprietaryTool::factory()->create([
            'description' => null,
        ]);

        $alternative = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Queued Metrics Alternative',
            'slug' => 'queued-metrics-alternative',
            'repo_url' => 'https://github.com/example/queued-project',
            'license_type' => null,
            'primary_language' => null,
            'is_published' => true,
        ]);

        Http::fake([
            'https://api.github.com/repos/example/queued-project' => Http::response([
                'stargazers_count' => 1200,
                'forks_count' => 150,
                'open_issues_count' => 12,
                'pushed_at' => '2026-11-14T10:00:00Z',
                'license' => ['spdx_id' => 'MIT'],
                'default_branch' => 'main',
                'language' => 'PHP',
            ], 200),
            'https://api.github.com/repos/example/queued-project/languages' => Http::response([
                'PHP' => 50000,
                'JavaScript' => 1000,
            ], 200),
        ]);
        Http::preventStrayRequests();

        dispatch(new SyncGitHubMetricsJob($alternative));

        $this->assertDatabaseCount('jobs', 1);

        $this->artisan('queue:work', [
            '--once' => true,
            '--sleep' => 0,
            '--tries' => 3,
        ])->assertExitCode(0);

        $this->assertDatabaseCount('jobs', 0);

        $metric = RepoMetric::query()
            ->where('open_source_alternative_id', $alternative->id)
            ->firstOrFail();

        $this->assertSame(1200, $metric->github_stars);
        $this->assertSame(150, $metric->github_forks);
        $this->assertSame(12, $metric->open_issues);
        $this->assertSame('MIT', $metric->verified_license);
        $this->assertSame('PHP', $alternative->fresh()->primary_language);
        $this->assertSame('MIT', $alternative->fresh()->license_type);
        $this->assertSame(['PHP' => 50000, 'JavaScript' => 1000], $metric->languages);

        Http::assertSentCount(2);
    }
    public function test_transient_github_api_failures_are_thrown_so_the_worker_can_retry(): void
    {
        $tool = ProprietaryTool::factory()->create(['description' => null]);

        $alternative = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'repo_url' => 'https://github.com/example/retry-project',
            'is_published' => true,
        ]);

        Http::fake([
            'https://api.github.com/repos/example/retry-project' => Http::response([
                'message' => 'Upstream unavailable',
            ], 503),
        ]);
        Http::preventStrayRequests();

        $job = new SyncGitHubMetricsJob($alternative);

        $this->assertSame(3, $job->tries);
        $this->assertSame(60, $job->backoff);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP 503');

        $job->handle();
    }

}
