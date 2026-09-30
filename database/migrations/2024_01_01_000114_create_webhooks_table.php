<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhooks')) {
            Schema::create('webhooks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url', 500);
                $table->string('secret', 120)->nullable();
                $table->json('events'); // alternative.published, health.drop, collection.published
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('success_count')->default(0);
                $table->unsignedInteger('failure_count')->default(0);
                $table->timestamp('last_triggered_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->index('is_active', 'wh_active_idx');
            });
        }

        if (! Schema::hasTable('webhook_deliveries')) {
            Schema::create('webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('webhook_id')->constrained('webhooks')->cascadeOnDelete();
                $table->string('event', 60);
                $table->unsignedSmallInteger('status_code')->nullable();
                $table->boolean('success')->default(false);
                $table->text('response_body')->nullable();
                $table->text('error')->nullable();
                $table->timestamps();

                $table->index(['webhook_id', 'created_at'], 'whd_wh_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
