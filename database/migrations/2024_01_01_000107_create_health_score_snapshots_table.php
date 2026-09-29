<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
                $table->index(['open_source_alternative_id', 'recorded_at'], 'hss_alt_recorded_idx');
            });

            return;
        }

        // Table may exist from a failed earlier run without the composite index
        Schema::table('health_score_snapshots', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = [];
            try {
                $indexes = array_keys($sm->listTableIndexes('health_score_snapshots'));
            } catch (\Throwable) {
                // Doctrine may be unavailable on some hosts — try adding and ignore duplicate
            }

            if (! in_array('hss_alt_recorded_idx', $indexes, true)
                && ! in_array('health_score_snapshots_open_source_alternative_id_recorded_at_index', $indexes, true)) {
                try {
                    $table->index(['open_source_alternative_id', 'recorded_at'], 'hss_alt_recorded_idx');
                } catch (\Throwable) {
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_score_snapshots');
    }
};
