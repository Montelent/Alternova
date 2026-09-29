<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('slug_redirects')) {
            Schema::create('slug_redirects', function (Blueprint $table) {
                $table->id();
                $table->string('old_slug', 190)->index();
                $table->string('new_slug', 190)->index();
                $table->string('model_type', 80)->default('alternative')->index();
                $table->timestamps();
                $table->unique(['old_slug', 'model_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('slug_redirects');
    }
};
