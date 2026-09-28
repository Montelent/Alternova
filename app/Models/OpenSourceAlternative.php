<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

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
        'votes_count',
        'is_published',
        'is_featured',
        'meta_title',
        'meta_description',
        'editor_note',
        'pros',
        'cons',
        'repo_reachable',
        'website_reachable',
        'links_checked_at',
    ];

    protected $casts = [
        'self_host_difficulty' => 'integer',
        'overall_health_score' => 'float',
        'votes_count' => 'integer',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'pros' => 'array',
        'cons' => 'array',
        'repo_reachable' => 'boolean',
        'website_reachable' => 'boolean',
        'links_checked_at' => 'datetime',
    ];

    public function proprietaryTool(): BelongsTo
    {
        return $this->belongsTo(ProprietaryTool::class);
    }

    public function repoMetric(): HasOne
    {
        return $this->hasOne(RepoMetric::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AlternativeVote::class);
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

    public function seoTitle(): string
    {
        if ($this->meta_title) {
            return $this->meta_title;
        }

        $prop = $this->proprietaryTool?->name ?? 'proprietary tools';

        return $this->name.' — Open-Source '.$prop.' Alternative | Alternova';
    }

    public function seoDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        return str(
            $this->name.' is a free, self-hostable open-source alternative to '
            .($this->proprietaryTool?->name ?? 'proprietary software').'. '
            .($this->description ?? '')
        )->limit(155)->toString();
    }

    public function hasBrokenLinks(): bool
    {
        if ($this->links_checked_at === null) {
            return false;
        }

        return $this->repo_reachable === false || $this->website_reachable === false;
    }

    public function recalculateHealthScore(): float
    {
        $metric = RepoMetric::query()
            ->where('open_source_alternative_id', $this->id)
            ->first();

        if (! $metric) {
            $this->forceFill(['overall_health_score' => 0])->save();

            return 0.0;
        }

        $stars = max((int) $metric->github_stars, 0);
        $forks = max((int) $metric->github_forks, 0);
        $issues = max((int) $metric->open_issues, 0);

        $starsScore = min(log10(max($stars, 1)) * 15, 40);
        $forksScore = min(log10(max($forks, 1)) * 10, 20);
        $issuesScore = $issues < 50 ? 15 : max(0, 15 - ($issues / 20));

        $recencyScore = 0;
        if ($metric->last_commit_at) {
            if ($metric->last_commit_at->gt(now()->subMonths(3))) {
                $recencyScore = 25;
            } elseif ($metric->last_commit_at->gt(now()->subYear())) {
                $recencyScore = 10;
            }
        }

        $score = round(min($starsScore + $forksScore + $issuesScore + $recencyScore, 100), 2);

        $this->forceFill(['overall_health_score' => $score])->save();

        return $score;
    }
}
