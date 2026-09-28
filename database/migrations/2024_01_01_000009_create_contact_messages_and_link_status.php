<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('unread'); // unread, read, archived
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            if (! Schema::hasColumn('open_source_alternatives', 'repo_reachable')) {
                $table->boolean('repo_reachable')->nullable()->after('repo_url');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'website_reachable')) {
                $table->boolean('website_reachable')->nullable()->after('website_url');
            }
            if (! Schema::hasColumn('open_source_alternatives', 'links_checked_at')) {
                $table->timestamp('links_checked_at')->nullable()->after('website_reachable');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');

        Schema::table('open_source_alternatives', function (Blueprint $table) {
            $cols = ['repo_reachable', 'website_reachable', 'links_checked_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('open_source_alternatives', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
