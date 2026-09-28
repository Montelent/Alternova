<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proprietary_tools', function (Blueprint $table) {
            if (! Schema::hasColumn('proprietary_tools', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('target_audience');
            }
            if (! Schema::hasColumn('proprietary_tools', 'meta_description')) {
                $table->string('meta_description', 320)->nullable()->after('meta_title');
            }
        });

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('open_source_alternatives', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('is_featured');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'meta_description')) {
                $table->string('meta_description', 320)->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'editor_note')) {
                $table->text('editor_note')->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'pros')) {
                $table->json('pros')->nullable()->after('editor_note');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'cons')) {
                $table->json('cons')->nullable()->after('pros');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proprietary_tools', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description']);
        });

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'editor_note', 'pros', 'cons']);
        });
    }
};
