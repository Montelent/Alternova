<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhook_deliveries')) {
            return;
        }

        if (! Schema::hasColumn('webhook_deliveries', 'payload')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                $table->longText('payload')->nullable()->after('event');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('webhook_deliveries') && Schema::hasColumn('webhook_deliveries', 'payload')) {
            Schema::table('webhook_deliveries', function (Blueprint $table) {
                $table->dropColumn('payload');
            });
        }
    }
};
