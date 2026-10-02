<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('open_source_alternatives')) {
            return;
        }

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('open_source_alternatives', 'logo_path')) {
                $table->string('logo_path', 500)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'logo_url')) {
                $table->string('logo_url', 500)->nullable()->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('open_source_alternatives')) {
            return;
        }

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (Schema::hasColumn('open_source_alternatives', 'logo_url')) {
                $table->dropColumn('logo_url');
            }
            if (Schema::hasColumn('open_source_alternatives', 'logo_path')) {
                $table->dropColumn('logo_path');
            }
        });
    }
};
