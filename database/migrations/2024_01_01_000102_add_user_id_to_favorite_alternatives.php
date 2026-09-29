<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('favorite_alternatives')) {
            Schema::create('favorite_alternatives', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 64)->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->unsignedBigInteger('open_source_alternative_id');
                $table->timestamps();
            });

            return;
        }

        Schema::table('favorite_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('favorite_alternatives', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }
            // session_id may be required in older schema — keep nullable if possible
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('favorite_alternatives') && Schema::hasColumn('favorite_alternatives', 'user_id')) {
            Schema::table('favorite_alternatives', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
