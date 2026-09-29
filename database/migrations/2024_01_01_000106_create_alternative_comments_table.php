<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('alternative_comments')) {
            return;
        }

        Schema::create('alternative_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('open_source_alternative_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('author_name', 80);
            $table->string('author_email', 190)->nullable();
            $table->text('body');
            $table->boolean('is_approved')->default(false)->index();
            $table->boolean('is_hidden')->default(false)->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_comments');
    }
};
