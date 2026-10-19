<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('admin_drafts')) {
            return;
        }

        Schema::create('admin_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('resource', 64);
            $table->unsignedBigInteger('record_id')->default(0); // 0 = create form
            $table->json('payload');
            $table->timestamps();

            $table->unique(['user_id', 'resource', 'record_id']);
            $table->index(['user_id', 'resource']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_drafts');
    }
};
