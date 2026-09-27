<?php

namespace Database\Factories;

use App\Models\OpenSourceAlternative;
use App\Models\RepoMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepoMetricFactory extends Factory
{
    protected $model = RepoMetric::class;

    public function definition(): array
    {
        return [
            'open_source_alternative_id' => OpenSourceAlternative::factory(),
            'github_stars' => $this->faker->numberBetween(10, 50000),
            'github_forks' => $this->faker->numberBetween(5, 5000),
            'open_issues' => $this->faker->numberBetween(0, 200),
            'last_commit_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'verified_license' => $this->faker->randomElement(['MIT', 'Apache-2.0', 'AGPL-3.0']),
            'default_branch' => 'main',
            'languages' => ['PHP' => 60, 'JavaScript' => 30, 'CSS' => 10],
            'synced_at' => now(),
        ];
    }
}
