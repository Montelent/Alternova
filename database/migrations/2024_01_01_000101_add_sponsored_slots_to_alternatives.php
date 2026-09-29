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
            if (! Schema::hasColumn('open_source_alternatives', 'is_sponsored')) {
                $table->boolean('is_sponsored')->default(false);
            }
            if (! Schema::hasColumn('open_source_alternatives', 'sponsored_until')) {
                $table->timestamp('sponsored_until')->nullable();
            }
            if (! Schema::hasColumn('open_source_alternatives', 'sponsor_label')) {
                $table->string('sponsor_label', 80)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('open_source_alternatives')) {
            return;
        }

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            foreach (['is_sponsored', 'sponsored_until', 'sponsor_label'] as $col) {
                if (Schema::hasColumn('open_source_alternatives', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
