<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('open_source_alternatives')) {
            Schema::table('open_source_alternatives', function (Blueprint $table) {
                if (! Schema::hasColumn('open_source_alternatives', 'focus_keyword')) {
                    $table->string('focus_keyword', 120)->nullable()->after('meta_description');
                }
                if (! Schema::hasColumn('open_source_alternatives', 'robots_meta')) {
                    $table->string('robots_meta', 80)->nullable()->after('focus_keyword');
                }
                if (! Schema::hasColumn('open_source_alternatives', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable()->after('robots_meta');
                }
                if (! Schema::hasColumn('open_source_alternatives', 'og_title')) {
                    $table->string('og_title', 120)->nullable()->after('canonical_url');
                }
                if (! Schema::hasColumn('open_source_alternatives', 'og_description')) {
                    $table->string('og_description', 200)->nullable()->after('og_title');
                }
                if (! Schema::hasColumn('open_source_alternatives', 'og_image_url')) {
                    $table->string('og_image_url', 500)->nullable()->after('og_description');
                }
            });
        }

        if (Schema::hasTable('proprietary_tools')) {
            Schema::table('proprietary_tools', function (Blueprint $table) {
                if (! Schema::hasColumn('proprietary_tools', 'focus_keyword')) {
                    $table->string('focus_keyword', 120)->nullable()->after('meta_description');
                }
                if (! Schema::hasColumn('proprietary_tools', 'robots_meta')) {
                    $table->string('robots_meta', 80)->nullable()->after('focus_keyword');
                }
                if (! Schema::hasColumn('proprietary_tools', 'canonical_url')) {
                    $table->string('canonical_url', 500)->nullable()->after('robots_meta');
                }
                if (! Schema::hasColumn('proprietary_tools', 'og_title')) {
                    $table->string('og_title', 120)->nullable()->after('canonical_url');
                }
                if (! Schema::hasColumn('proprietary_tools', 'og_description')) {
                    $table->string('og_description', 200)->nullable()->after('og_title');
                }
                if (! Schema::hasColumn('proprietary_tools', 'og_image_url')) {
                    $table->string('og_image_url', 500)->nullable()->after('og_description');
                }
            });
        }
    }

    public function down(): void
    {
        $cols = ['focus_keyword', 'robots_meta', 'canonical_url', 'og_title', 'og_description', 'og_image_url'];

        foreach (['open_source_alternatives', 'proprietary_tools'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $blueprint) use ($table, $cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn($table, $col)) {
                        $blueprint->dropColumn($col);
                    }
                }
            });
        }
    }
};
