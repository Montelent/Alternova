<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repo_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_source_alternative_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('github_stars')->default(0);
            $table->unsignedBigInteger('github_forks')->default(0);
            $table->unsignedInteger('open_issues')->default(0);
            $table->timestamp('last_commit_at')->nullable();
            $table->string('verified_license')->nullable();
            $table->string('default_branch')->nullable();
            $table->json('languages')->nullable(); // full language breakdown
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique('open_source_alternative_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repo_metrics');
    }
};
