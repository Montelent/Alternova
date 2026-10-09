<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class ProprietaryTool extends Model
{
    use HasFactory, SoftDeletes, HasTags, Searchable;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'website_url',
        'description',
        'key_features',
        'target_audience',
        'is_published',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'robots_meta',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_url',
    ];

    protected $casts = [
        'key_features' => 'array',
        'is_published' => 'boolean',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        return MediaUrl::make($this->logo_path, 'uploads')
            ?? MediaUrl::make($this->logo_path, 'public');
    }

    /**
     * Primary FK link (open_source_alternatives.proprietary_tool_id).
     */
    public function openSourceAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class);
    }

    public function publishedAlternatives(): HasMany
    {
        return $this->hasMany(OpenSourceAlternative::class)
            ->where('is_published', true);
    }

    /**
     * Multi-select pivot links (alternative_proprietary_tool).
     */
    public function alternativesMany(): BelongsToMany
    {
        return $this->belongsToMany(
            OpenSourceAlternative::class,
            'alternative_proprietary_tool',
            'proprietary_tool_id',
            'open_source_alternative_id'
        )->withPivot('position')->withTimestamps();
    }

    /**
     * Unique alternatives linked via FK and/or pivot (published only for public pages).
     */
    public function linkedAlternatives()
    {
        $primary = $this->publishedAlternatives()
            ->with(['repoMetric', 'tags'])
            ->orderByDesc('overall_health_score')
            ->get();

        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $viaPivot = OpenSourceAlternative::query()
                    ->where('is_published', true)
                    ->whereHas('proprietaryTools', fn ($q) => $q->where('proprietary_tools.id', $this->id))
                    ->with(['repoMetric', 'tags'])
                    ->orderByDesc('overall_health_score')
                    ->get();

                return $primary->concat($viaPivot)->unique('id')->sortByDesc('overall_health_score')->values();
            }
        } catch (\Throwable) {
        }

        return $primary;
    }

    /**
     * Admin / SEO count: every alternative linked to this tool (FK + pivot), not double-counted.
     * Includes drafts so the admin list matches what editors assigned.
     */
    public function alternativesTotalCount(): int
    {
        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                $sql = <<<'SQL'
SELECT COUNT(*) FROM (
    SELECT osa.id
    FROM open_source_alternatives osa
    WHERE osa.proprietary_tool_id = ?
      AND osa.deleted_at IS NULL
    UNION
    SELECT apt.open_source_alternative_id AS id
    FROM alternative_proprietary_tool apt
    INNER JOIN open_source_alternatives osa2
        ON osa2.id = apt.open_source_alternative_id
       AND osa2.deleted_at IS NULL
    WHERE apt.proprietary_tool_id = ?
) AS linked
SQL;

                return (int) DB::selectOne($sql, [$this->id, $this->id])->count;
            }
        } catch (\Throwable) {
        }

        return (int) $this->openSourceAlternatives()->count();
    }

    /**
     * Eager/subquery count for Filament tables (avoids N+1).
     */
    public function scopeWithAlternativesTotalCount(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();

        try {
            if (Schema::hasTable('alternative_proprietary_tool')) {
                return $query->select("{$table}.*")->selectSub(function ($sub) use ($table) {
                    $sub->from('open_source_alternatives as osa')
                        ->selectRaw('COUNT(DISTINCT osa.id)')
                        ->where(function ($w) use ($table) {
                            $w->whereColumn('osa.proprietary_tool_id', "{$table}.id")
                                ->orWhereExists(function ($e) use ($table) {
                                    $e->selectRaw('1')
                                        ->from('alternative_proprietary_tool as apt')
                                        ->whereColumn('apt.open_source_alternative_id', 'osa.id')
                                        ->whereColumn('apt.proprietary_tool_id', "{$table}.id");
                                });
                        })
                        ->whereNull('osa.deleted_at');
                }, 'alternatives_total_count');
            }
        } catch (\Throwable) {
        }

        return $query->withCount(['openSourceAlternatives as alternatives_total_count']);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $field = $field ?: $this->getRouteKeyName();

        return static::query()
            ->where($field, $value)
            ->where('is_published', true)
            ->first();
    }

    public function publicUrl(): string
    {
        return route('alternativesto.show', $this->slug);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'target_audience' => $this->target_audience,
            'key_features' => $this->key_features,
        ];
    }
}
