<?php

namespace Tests\Feature;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Services\LinkHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkHealthServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_link_checker_uses_head_requests_and_persists_reachability_results(): void
    {
        $tool = ProprietaryTool::factory()->create(['description' => null]);
        $alternative = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'repo_url' => 'https://1.1.1.1/repository',
            'website_url' => 'https://1.0.0.1/home',
            'description' => null,
        ]);

        Http::fake([
            'https://1.1.1.1/repository' => Http::response('', 200),
            'https://1.0.0.1/home' => Http::response('', 302),
        ]);
        Http::preventStrayRequests();

        $checked = app(LinkHealthService::class)->checkAlternative($alternative);

        $this->assertTrue($checked->repo_reachable);
        $this->assertTrue($checked->website_reachable);
        $this->assertNotNull($checked->links_checked_at);

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'HEAD');
    }

    public function test_link_checker_rejects_private_destinations_without_sending_requests(): void
    {
        Http::preventStrayRequests();

        $this->assertFalse(app(LinkHealthService::class)->isReachable('http://127.0.0.1/admin'));
        $this->assertFalse(app(LinkHealthService::class)->isReachable('http://169.254.169.254/latest/meta-data'));

        Http::assertNothingSent();
    }
}
