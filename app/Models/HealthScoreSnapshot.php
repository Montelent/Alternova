<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthScoreSnapshot extends Model
{
    protected $fillable = [
        'open_source_alternative_id',
        'score',
        'github_stars',
        'github_forks',
        'open_issues',
        'recorded_at',
    ];

    protected $casts = [
        'score' => 'float',
        'github_stars' => 'integer',
        'github_forks' => 'integer',
        'open_issues' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(OpenSourceAlternative::class, 'open_source_alternative_id');
    }
}
