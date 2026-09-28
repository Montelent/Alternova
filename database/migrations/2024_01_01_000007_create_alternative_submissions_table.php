<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternative_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submitter_name')->nullable();
            $table->string('submitter_email')->nullable();
            $table->string('proprietary_name');
            $table->string('alternative_name');
            $table->string('repo_url');
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->string('license_type')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('created_alternative_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_submissions');
    }
};
