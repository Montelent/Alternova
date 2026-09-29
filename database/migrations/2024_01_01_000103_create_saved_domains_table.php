<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saved_domains')) {
            Schema::create('saved_domains', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('session_id', 64)->nullable()->index();
                $table->string('domain', 255);
                $table->unsignedSmallInteger('brandability')->nullable();
                $table->string('status', 40)->nullable();
                $table->timestamps();
            });

            return;
        }

        Schema::table('saved_domains', function (Blueprint $table) {
            if (! Schema::hasColumn('saved_domains', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->index();
            }
            if (! Schema::hasColumn('saved_domains', 'brandability')) {
                $table->unsignedSmallInteger('brandability')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_domains');
    }
};
