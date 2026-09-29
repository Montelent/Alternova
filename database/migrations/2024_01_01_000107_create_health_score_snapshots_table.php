<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('health_score_snapshots')) {
            return;
        }

        Schema::create('health_score_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('open_source_alternative_id')->index();
            $table->decimal('score', 5, 2);
            $table->unsignedInteger('github_stars')->nullable();
            $table->unsignedInteger('github_forks')->nullable();
            $table->unsignedInteger('open_issues')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['open_source_alternative_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_score_snapshots');
    }
};
