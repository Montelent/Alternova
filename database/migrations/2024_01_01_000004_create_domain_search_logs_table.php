<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_search_logs', function (Blueprint $table) {
            $table->id();
            $table->json('seed_keywords');
            $table->json('selected_tlds')->nullable();
            $table->unsignedInteger('domain_generated_count')->default(0);
            $table->unsignedInteger('available_count')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_search_logs');
    }
};
