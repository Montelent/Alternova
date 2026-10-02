<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contact_messages')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                if (! Schema::hasColumn('contact_messages', 'public_id')) {
                    $table->string('public_id', 24)->nullable()->unique()->after('id');
                }
                if (! Schema::hasColumn('contact_messages', 'access_token')) {
                    $table->string('access_token', 64)->nullable()->after('public_id');
                }
                if (! Schema::hasColumn('contact_messages', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('access_token')->constrained()->nullOnDelete();
                }
                if (! Schema::hasColumn('contact_messages', 'ticket_status')) {
                    $table->string('ticket_status', 32)->default('open')->after('status');
                }
                if (! Schema::hasColumn('contact_messages', 'last_reply_at')) {
                    $table->timestamp('last_reply_at')->nullable()->after('read_at');
                }
                if (! Schema::hasColumn('contact_messages', 'last_reply_by')) {
                    $table->string('last_reply_by', 16)->nullable()->after('last_reply_at');
                }
            });
        }

        if (! Schema::hasTable('contact_message_replies')) {
            Schema::create('contact_message_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('contact_message_id')->constrained('contact_messages')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('author_type', 16); // staff | user
                $table->string('author_name')->nullable();
                $table->string('author_email')->nullable();
                $table->text('body');
                $table->boolean('is_internal')->default(false);
                $table->timestamps();

                $table->index(['contact_message_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_replies');

        if (Schema::hasTable('contact_messages')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                foreach (['last_reply_by', 'last_reply_at', 'ticket_status', 'user_id', 'access_token', 'public_id'] as $col) {
                    if (Schema::hasColumn('contact_messages', $col)) {
                        if ($col === 'user_id') {
                            $table->dropConstrainedForeignId('user_id');
                        } else {
                            $table->dropColumn($col);
                        }
                    }
                }
            });
        }
    }
};
