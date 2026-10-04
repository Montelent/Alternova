<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite / filter indexes for high-traffic public queries.
 * Safe to re-run: skips indexes that already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexes('open_source_alternatives', [
            'osa_published_health' => ['is_published', 'overall_health_score'],
            'osa_published_votes' => ['is_published', 'votes_count'],
            'osa_published_created' => ['is_published', 'created_at'],
            'osa_published_featured' => ['is_published', 'is_featured'],
            'osa_published_language' => ['is_published', 'primary_language'],
            'osa_published_license' => ['is_published', 'license_type'],
            'osa_deleted_at' => ['deleted_at'],
        ]);

        $this->addIndexes('proprietary_tools', [
            'pt_published_name' => ['is_published', 'name'],
            'pt_deleted_at' => ['deleted_at'],
        ]);

        if (Schema::hasTable('repo_metrics')) {
            $this->addIndexes('repo_metrics', [
                'rm_stars' => ['github_stars'],
                'rm_alt_id' => ['open_source_alternative_id'],
            ]);
        }

        if (Schema::hasTable('alternative_votes')) {
            $this->addIndexes('alternative_votes', [
                'av_alt_created' => ['open_source_alternative_id', 'created_at'],
                'av_created' => ['created_at'],
            ]);
        }

        if (Schema::hasTable('collections')) {
            $this->addIndexes('collections', [
                'col_published_updated' => ['is_published', 'updated_at'],
            ]);
        }

        if (Schema::hasTable('pages')) {
            $this->addIndexes('pages', [
                'pages_published_slug' => ['is_published', 'slug'],
            ]);
        }

        if (Schema::hasTable('favorite_alternatives')) {
            $this->addIndexes('favorite_alternatives', [
                'fav_session' => ['session_id'],
                'fav_user' => ['user_id'],
                'fav_alt' => ['open_source_alternative_id'],
            ]);
        }

        if (Schema::hasTable('sessions')) {
            $this->addIndexes('sessions', [
                'sessions_last_activity' => ['last_activity'],
            ]);
        }
    }

    public function down(): void
    {
        // Indexes are additive performance aids; leave in place on rollback.
    }

    /**
     * @param  array<string, list<string>>  $indexes
     */
    protected function addIndexes(string $table, array $indexes): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
            foreach ($indexes as $name => $columns) {
                if ($this->indexExists($table, $name)) {
                    continue;
                }

                // Skip if any column is missing (older installs)
                foreach ($columns as $col) {
                    if (! Schema::hasColumn($table, $col)) {
                        continue 2;
                    }
                }

                try {
                    $blueprint->index($columns, $name);
                } catch (\Throwable) {
                    // Duplicate or engine limit — ignore
                }
            }
        });
    }

    protected function indexExists(string $table, string $name): bool
    {
        try {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);

            return isset($indexes[$name]);
        } catch (\Throwable) {
            // Doctrine may be unavailable on newer Laravel — try information_schema
            try {
                $db = Schema::getConnection()->getDatabaseName();
                $row = Schema::getConnection()->selectOne(
                    'SELECT COUNT(*) AS c FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                    [$db, $table, $name]
                );

                return ((int) ($row->c ?? 0)) > 0;
            } catch (\Throwable) {
                return false;
            }
        }
    }
};
