<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alternative_submissions') && ! Schema::hasColumn('alternative_submissions', 'tracking_token')) {
            Schema::table('alternative_submissions', function (Blueprint $table) {
                $table->string('tracking_token', 64)->nullable()->unique()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('alternative_submissions') && Schema::hasColumn('alternative_submissions', 'tracking_token')) {
            Schema::table('alternative_submissions', function (Blueprint $table) {
                $table->dropColumn('tracking_token');
            });
        }
    }
};
