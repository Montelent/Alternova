<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alternative_pros_cons')) {
            Schema::create('alternative_pros_cons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('open_source_alternative_id')->constrained('open_source_alternatives')->cascadeOnDelete();
                $table->string('type', 8); // pro | con
                $table->string('body', 280);
                $table->unsignedInteger('votes_count')->default(0);
                $table->boolean('is_approved')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->string('author_name')->nullable();
                $table->string('author_email')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['open_source_alternative_id', 'type', 'is_approved'], 'pc_alt_type_appr_idx');
                $table->index(['open_source_alternative_id', 'votes_count'], 'pc_alt_votes_idx');
            });
        }

        if (! Schema::hasTable('pros_cons_votes')) {
            Schema::create('pros_cons_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alternative_pros_con_id')->constrained('alternative_pros_cons')->cascadeOnDelete();
                $table->string('voter_key', 64);
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->unique(['alternative_pros_con_id', 'voter_key'], 'pc_vote_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pros_cons_votes');
        Schema::dropIfExists('alternative_pros_cons');
    }
};
