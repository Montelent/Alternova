<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'api_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('api_enabled')->default(true)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'api_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('api_enabled');
            });
        }
    }
};
