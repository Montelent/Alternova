<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('open_source_alternatives', 'changelog')) {
                $table->text('changelog')->nullable()->after('editor_note');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'gallery_urls')) {
                $table->json('gallery_urls')->nullable()->after('changelog');
            }
        });

        if (! Schema::hasTable('saved_domains')) {
            Schema::create('saved_domains', function (Blueprint $table) {
                $table->id();
                $table->string('session_id', 100)->index();
                $table->string('domain', 255);
                $table->unsignedTinyInteger('brandability')->nullable();
                $table->string('status', 32)->nullable();
                $table->timestamps();

                $table->unique(['session_id', 'domain']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_domains');

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (Schema::hasColumn('open_source_alternatives', 'gallery_urls')) {
                $table->dropColumn('gallery_urls');
            }
            if (Schema::hasColumn('open_source_alternatives', 'changelog')) {
                $table->dropColumn('changelog');
            }
        });
    }
};
