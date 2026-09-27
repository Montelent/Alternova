<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_source_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proprietary_tool_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('repo_url');
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('license_type')->nullable(); // MIT, Apache-2.0, AGPL-3.0, etc.
            $table->unsignedTinyInteger('self_host_difficulty')->default(3); // 1-5
            $table->longText('docker_compose_blueprint')->nullable();
            $table->string('primary_language')->nullable();
            $table->decimal('overall_health_score', 5, 2)->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['proprietary_tool_id', 'is_published']);
            $table->index('overall_health_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_source_alternatives');
    }
};
