<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('health_score_snapshots')) {
            Schema::create('health_score_snapshots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('open_source_alternative_id');
                $table->decimal('score', 5, 2);
                $table->unsignedInteger('github_stars')->nullable();
                $table->unsignedInteger('github_forks')->nullable();
                $table->unsignedInteger('open_issues')->nullable();
                $table->timestamp('recorded_at');
                $table->timestamps();

                // Short name — MySQL identifier limit is 64 chars
                $table->index(
                    ['open_source_alternative_id', 'recorded_at'],
                    'hss_alt_recorded_idx'
                );
            });

            return;
        }

        // Repair: table may exist from a failed earlier run without a usable composite index
        if (! $this->hasIndex('health_score_snapshots', 'hss_alt_recorded_idx')) {
            try {
                Schema::table('health_score_snapshots', function (Blueprint $table) {
                    $table->index(
                        ['open_source_alternative_id', 'recorded_at'],
                        'hss_alt_recorded_idx'
                    );
                });
            } catch (\Throwable) {
                // Index may already exist under another name — safe to continue
            }
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        try {
            $db = Schema::getConnection()->getDatabaseName();
            $row = DB::selectOne(
                'SELECT COUNT(*) AS c FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$db, $table, $indexName]
            );

            return ((int) ($row->c ?? 0)) > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('health_score_snapshots');
    }
};
