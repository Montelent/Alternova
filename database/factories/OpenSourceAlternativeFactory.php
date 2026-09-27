<?php

namespace Database\Factories;

use App\Models\OpenSourceAlternative;
use App\Models\ProprietaryTool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OpenSourceAlternativeFactory extends Factory
{
    protected $model = OpenSourceAlternative::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'proprietary_tool_id' => ProprietaryTool::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'repo_url' => 'https://github.com/' . $this->faker->userName() . '/' . Str::slug($name),
            'website_url' => $this->faker->url(),
            'description' => $this->faker->paragraphs(2, true),
            'license_type' => $this->faker->randomElement(['MIT', 'Apache-2.0', 'AGPL-3.0', 'GPL-3.0', 'BSD-3-Clause']),
            'self_host_difficulty' => $this->faker->numberBetween(1, 5),
            'docker_compose_blueprint' => "version: '3.8'\nservices:\n  app:\n    image: example/app:latest\n    ports:\n      - '8080:80'\n",
            'primary_language' => $this->faker->randomElement(['PHP', 'JavaScript', 'Python', 'Go', 'Rust', 'TypeScript']),
            'overall_health_score' => $this->faker->randomFloat(2, 20, 95),
            'is_published' => true,
            'is_featured' => $this->faker->boolean(20),
        ];
    }
}
