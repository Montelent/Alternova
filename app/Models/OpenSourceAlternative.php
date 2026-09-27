<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;
use Laravel\Scout\Searchable;

class OpenSourceAlternative extends Model
{
    use HasFactory, SoftDeletes, HasTags, Searchable;

    protected $fillable = [
        'proprietary_tool_id',
        'name',
        'slug',
        'repo_url',
        'website_url',
        'description',
        'license_type',
        'self_host_difficulty',
        'docker_compose_blueprint',
        'primary_language',
        'overall_health_score',
        'is_published',
        'is_featured',
    ];

    protected $casts = [
        'self_host_difficulty' => 'integer',
        'overall_health_score' => 'float',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function proprietaryTool(): BelongsTo
    {
        return $this->belongsTo(ProprietaryTool::class);
    }

    public function repoMetric(): HasOne
    {
        return $this->hasOne(RepoMetric::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'license_type' => $this->license_type,
            'self_host_difficulty' => $this->self_host_difficulty,
            'primary_language' => $this->primary_language,
            'overall_health_score' => $this->overall_health_score,
            'proprietary_tool_name' => $this->proprietaryTool?->name,
            'tags' => $this->tags->pluck('name')->toArray(),
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function recalculateHealthScore(): void
    {
        $metric = $this->repoMetric;

        if (!$metric) {
            $this->update(['overall_health_score' => 0]);
            return;
        }

        // Weighted scoring algorithm
        $starsScore = min(log10(max($metric->github_stars, 1)) * 15, 40);
        $forksScore = min(log10(max($metric->github_forks, 1)) * 10, 20);
        $issuesScore = $metric->open_issues < 50 ? 15 : max(0, 15 - ($metric->open_issues / 20));
        $recencyScore = $metric->last_commit_at && $metric->last_commit_at->gt(now()->subMonths(3))
            ? 25
            : ($metric->last_commit_at && $metric->last_commit_at->gt(now()->subYear()) ? 10 : 0);

        $score = round($starsScore + $forksScore + $issuesScore + $recencyScore, 2);

        $this->update(['overall_health_score' => min($score, 100)]);
    }
}
