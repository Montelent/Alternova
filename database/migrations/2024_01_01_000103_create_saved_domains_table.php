<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_domains')) {
            return;
        }

        Schema::create('saved_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('session_id', 64)->nullable()->index();
            $table->string('domain', 255);
            $table->string('status', 40)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_domains');
    }
};
