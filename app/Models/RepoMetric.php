<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepoMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_source_alternative_id',
        'github_stars',
        'github_forks',
        'open_issues',
        'last_commit_at',
        'verified_license',
        'default_branch',
        'languages',
        'synced_at',
    ];

    protected $casts = [
        'github_stars' => 'integer',
        'github_forks' => 'integer',
        'open_issues' => 'integer',
        'last_commit_at' => 'datetime',
        'languages' => 'array',
        'synced_at' => 'datetime',
    ];

    public function openSourceAlternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class);
    }
}
