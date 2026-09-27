<?php

namespace Database\Factories;

use App\Models\ProprietaryTool;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProprietaryToolFactory extends Factory
{
    protected $model = ProprietaryTool::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo_path' => null,
            'website_url' => $this->faker->url(),
            'description' => $this->faker->paragraphs(3, true),
            'key_features' => $this->faker->words(8),
            'target_audience' => $this->faker->randomElement(['Developers', 'Marketers', 'Startups', 'Enterprises']),
            'is_published' => true,
        ];
    }
}
