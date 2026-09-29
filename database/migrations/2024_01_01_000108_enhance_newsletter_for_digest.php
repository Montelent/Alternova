<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('newsletter_subscribers')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                if (! Schema::hasColumn('newsletter_subscribers', 'unsubscribe_token')) {
                    $table->string('unsubscribe_token', 64)->nullable()->unique();
                }
                if (! Schema::hasColumn('newsletter_subscribers', 'last_digest_at')) {
                    $table->timestamp('last_digest_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('newsletter_digest_logs')) {
            Schema::create('newsletter_digest_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('days')->default(7);
                $table->unsignedInteger('new_count')->default(0);
                $table->unsignedInteger('updated_count')->default(0);
                $table->unsignedInteger('subscriber_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->boolean('dry_run')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_digest_logs');
    }
};
