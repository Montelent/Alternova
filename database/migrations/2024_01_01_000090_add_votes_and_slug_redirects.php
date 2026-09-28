<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('open_source_alternatives', 'votes_count')) {
                $table->unsignedInteger('votes_count')->default(0)->after('overall_health_score');
            }
        });

        if (! Schema::hasTable('alternative_votes')) {
            Schema::create('alternative_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('open_source_alternative_id')->constrained()->cascadeOnDelete();
                $table->string('voter_key', 64)->index();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->unique(['open_source_alternative_id', 'voter_key'], 'alt_vote_unique');
            });
        }

        if (! Schema::hasTable('slug_redirects')) {
            Schema::create('slug_redirects', function (Blueprint $table) {
                $table->id();
                $table->string('old_slug')->unique();
                $table->string('new_slug')->index();
                $table->string('model_type')->default('OpenSourceAlternative');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_votes');
        Schema::dropIfExists('slug_redirects');

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (Schema::hasColumn('open_source_alternatives', 'votes_count')) {
                $table->dropColumn('votes_count');
            }
        });
    }
};
