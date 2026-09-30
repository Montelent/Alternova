<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('collection_submissions')) {
            return;
        }

        Schema::create('collection_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submitter_name')->nullable();
            $table->string('submitter_email')->nullable();
            $table->string('title');
            $table->string('proposed_slug')->nullable();
            $table->text('description')->nullable();
            $table->text('intro')->nullable();
            $table->json('alternative_slugs')->nullable(); // list of suggested slugs
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->string('tracking_token', 64)->unique();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('created_collection_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('status', 'coll_sub_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_submissions');
    }
};
