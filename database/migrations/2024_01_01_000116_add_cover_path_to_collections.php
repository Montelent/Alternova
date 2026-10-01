<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('collections') && ! Schema::hasColumn('collections', 'cover_path')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->string('cover_path')->nullable()->after('cover_image_url');
            });
        }

        if (Schema::hasTable('open_source_alternatives') && ! Schema::hasColumn('open_source_alternatives', 'gallery_paths')) {
            Schema::table('open_source_alternatives', function (Blueprint $table) {
                $table->json('gallery_paths')->nullable()->after('gallery_urls');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('collections') && Schema::hasColumn('collections', 'cover_path')) {
            Schema::table('collections', function (Blueprint $table) {
                $table->dropColumn('cover_path');
            });
        }
        if (Schema::hasTable('open_source_alternatives') && Schema::hasColumn('open_source_alternatives', 'gallery_paths')) {
            Schema::table('open_source_alternatives', function (Blueprint $table) {
                $table->dropColumn('gallery_paths');
            });
        }
    }
};
