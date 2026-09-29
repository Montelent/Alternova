<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_activity_logs')) {
            Schema::create('admin_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action', 80);
                $table->string('subject_type', 120)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_label', 255)->nullable();
                $table->json('properties')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};
