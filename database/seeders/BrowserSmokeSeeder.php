<?php

namespace Database\Seeders;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Database\Seeder;

class BrowserSmokeSeeder extends Seeder
{
    public function run(): void
    {
        $tool = ProprietaryTool::factory()->create([
            'name' => 'Browser Comparison Tool',
            'slug' => 'browser-comparison-tool',
            'description' => null,
        ]);

        OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Browser Alpha Alternative',
            'slug' => 'browser-alpha-alternative',
            'repo_url' => 'https://github.com/example/browser-alpha',
            'description' => 'Browser test alternative alpha.',
            'license_type' => 'MIT',
            'primary_language' => 'PHP',
            'overall_health_score' => 91,
            'is_published' => true,
            'is_featured' => false,
            'is_sponsored' => false,
        ]);

        OpenSourceAlternative::factory()->create([
            'proprietary_tool_id' => $tool->id,
            'name' => 'Browser Beta Alternative',
            'slug' => 'browser-beta-alternative',
            'repo_url' => 'https://github.com/example/browser-beta',
            'description' => 'Browser test alternative beta.',
            'license_type' => 'Apache-2.0',
            'primary_language' => 'Rust',
            'overall_health_score' => 82,
            'is_published' => true,
            'is_featured' => false,
            'is_sponsored' => false,
        ]);
    }
}
