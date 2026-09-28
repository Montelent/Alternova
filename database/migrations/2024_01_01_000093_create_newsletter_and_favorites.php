<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('status', 32)->default('active'); // active, unsubscribed
                $table->string('source', 64)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('favorite_alternatives')) {
            Schema::create('favorite_alternatives', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 64)->index();
                $table->foreignId('open_source_alternative_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['session_id', 'open_source_alternative_id'], 'fav_session_alt_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_alternatives');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
