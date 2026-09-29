<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('affiliate_clicks')) {
            Schema::create('affiliate_clicks', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 40)->index();
                $table->string('domain', 255)->nullable()->index();
                $table->string('destination_url', 1000);
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->string('referer', 500)->nullable();
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
