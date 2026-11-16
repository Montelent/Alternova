<?php

namespace Tests\Feature;

use App\Models\ProprietaryTool;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SitemapScalingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Installer::lock();
        config(['seo.sitemap_page_size' => 1]);
    }

    protected function tearDown(): void
    {
        File::delete(Installer::lockPath());
        File::delete(storage_path('app/'.Installer::REOPEN_FILE));
        parent::tearDown();
    }

    public function test_sitemap_index_lists_each_required_page_for_large_child_sitemaps(): void
    {
        ProprietaryTool::factory()->count(2)->create(['is_published' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/sitemap-tools.xml', false)
            ->assertSee('/sitemap-tools.xml?page=2', false);
    }

    public function test_child_sitemap_honors_page_parameter_and_keeps_each_page_bounded(): void
    {
        $tools = ProprietaryTool::factory()->count(2)->create(['is_published' => true]);

        $response = $this->get('/sitemap-tools.xml?page=2')->assertOk();

        $response->assertSee('/alternativesto/'.$tools[1]->slug, false);
        $response->assertDontSee('/alternativesto/'.$tools[0]->slug, false);
    }

    public function test_invalid_sitemap_page_numbers_are_safely_clamped_to_first_page(): void
    {
        $tool = ProprietaryTool::factory()->create(['is_published' => true]);

        $this->get('/sitemap-tools.xml?page=-10')
            ->assertOk()
            ->assertSee('/alternativesto/'.$tool->slug, false);
    }
}
