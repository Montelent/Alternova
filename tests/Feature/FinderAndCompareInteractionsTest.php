<?php

namespace Tests\Feature;

use App\Livewire\CompareAlternatives;
use App\Livewire\OpenSourceFinder;
use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use App\Support\Installer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class FinderAndCompareInteractionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Installer::lock();
    }

    protected function tearDown(): void
    {
        File::delete(Installer::lockPath());
        File::delete(storage_path('app/'.Installer::REOPEN_FILE));
        parent::tearDown();
    }

    public function test_finder_filters_published_alternatives_by_license_language_difficulty_and_tool(): void
    {
        $tool = ProprietaryTool::factory()->create([
            'name' => 'Finder Parent Tool',
            'slug' => 'finder-parent-tool',
        ]);

        $match = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Finder Regression Alternative',
            'slug' => 'finder-regression-alternative',
            'license_type' => 'MIT',
            'primary_language' => 'Rust',
            'self_host_difficulty' => 2,
            'is_published' => true,
            'is_sponsored' => false,
        ]);

        OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Different Finder Alternative',
            'slug' => 'different-finder-alternative',
            'license_type' => 'Apache-2.0',
            'primary_language' => 'Python',
            'self_host_difficulty' => 4,
            'is_published' => true,
            'is_sponsored' => false,
        ]);

        Livewire::test(OpenSourceFinder::class)
            ->assertSee('Finder Regression Alternative')
            ->assertSee('Different Finder Alternative')
            ->set('licenses', ['MIT'])
            ->assertSee('Finder Regression Alternative')
            ->assertDontSee('Different Finder Alternative')
            ->set('languages', ['Rust'])
            ->assertSee('Finder Regression Alternative')
            ->set('difficulties', [2])
            ->assertSee('Finder Regression Alternative')
            ->set('toolSlug', $tool->slug)
            ->assertSee('Finder Regression Alternative')
            ->assertDontSee('Different Finder Alternative');

        $this->assertTrue($match->fresh()->is_published);
    }

    public function test_compare_page_renders_selected_alternatives_and_swaps_the_sides(): void
    {
        $tool = ProprietaryTool::factory()->create();

        $left = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Compare Alpha',
            'slug' => 'compare-alpha',
            'is_published' => true,
            'overall_health_score' => 90,
        ]);

        $right = OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Compare Beta',
            'slug' => 'compare-beta',
            'is_published' => true,
            'overall_health_score' => 75,
        ]);

        Livewire::test(CompareAlternatives::class)
            ->set('leftSlug', $left->slug)
            ->set('rightSlug', $right->slug)
            ->assertSee('Compare Alpha')
            ->assertSee('Compare Beta')
            ->assertSee('Health score')
            ->assertSee('Share this comparison')
            ->call('swap')
            ->assertSet('leftSlug', $right->slug)
            ->assertSet('rightSlug', $left->slug);
    }
}
