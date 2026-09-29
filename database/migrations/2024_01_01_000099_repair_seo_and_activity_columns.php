<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent repair: adds any missing SEO / activity columns
 * even if an earlier migration was skipped or partially applied.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIfMissing('open_source_alternatives', [
            'focus_keyword' => fn (Blueprint $t) => $t->string('focus_keyword', 120)->nullable(),
            'robots_meta' => fn (Blueprint $t) => $t->string('robots_meta', 80)->nullable(),
            'canonical_url' => fn (Blueprint $t) => $t->string('canonical_url', 500)->nullable(),
            'og_title' => fn (Blueprint $t) => $t->string('og_title', 120)->nullable(),
            'og_description' => fn (Blueprint $t) => $t->string('og_description', 200)->nullable(),
            'og_image_url' => fn (Blueprint $t) => $t->string('og_image_url', 500)->nullable(),
        ]);

        $this->addIfMissing('proprietary_tools', [
            'focus_keyword' => fn (Blueprint $t) => $t->string('focus_keyword', 120)->nullable(),
            'robots_meta' => fn (Blueprint $t) => $t->string('robots_meta', 80)->nullable(),
            'canonical_url' => fn (Blueprint $t) => $t->string('canonical_url', 500)->nullable(),
            'og_title' => fn (Blueprint $t) => $t->string('og_title', 120)->nullable(),
            'og_description' => fn (Blueprint $t) => $t->string('og_description', 200)->nullable(),
            'og_image_url' => fn (Blueprint $t) => $t->string('og_image_url', 500)->nullable(),
        ]);

        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action', 80);
                $table->string('subject_type', 120)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_label', 255)->nullable();
                $table->json('properties')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                $table->index(['subject_type', 'subject_id']);
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('slug_redirects')) {
            Schema::create('slug_redirects', function (Blueprint $table) {
                $table->id();
                $table->string('old_slug', 190)->index();
                $table->string('new_slug', 190)->index();
                $table->string('model_type', 80)->default('alternative')->index();
                $table->timestamps();
                $table->unique(['old_slug', 'model_type']);
            });
        }
    }

    /**
     * @param  array<string, callable(Blueprint): void>  $columns
     */
    protected function addIfMissing(string $table, array $columns): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($definition) {
                $definition($blueprint);
            });
        }
    }

    public function down(): void
    {
        // Non-destructive repair migration — no down drops.
    }
};
