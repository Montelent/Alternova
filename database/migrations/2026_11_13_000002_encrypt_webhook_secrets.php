<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('webhooks') || ! Schema::hasColumn('webhooks', 'secret')) {
            return;
        }

        Schema::table('webhooks', function (Blueprint $table) {
            $table->text('secret')->nullable()->change();
        });

        DB::table('webhooks')
            ->select(['id', 'secret'])
            ->whereNotNull('secret')
            ->orderBy('id')
            ->chunkById(100, function ($webhooks): void {
                foreach ($webhooks as $webhook) {
                    // A migration can be retried after a partial DDL/data failure.
                    // Do not encrypt a value that already has a valid Laravel payload.
                    try {
                        Crypt::decryptString((string) $webhook->secret);

                        continue;
                    } catch (\Throwable) {
                        // Plaintext legacy value: encrypt it below.
                    }

                    DB::table('webhooks')
                        ->where('id', $webhook->id)
                        ->update(['secret' => Crypt::encryptString((string) $webhook->secret)]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('webhooks') || ! Schema::hasColumn('webhooks', 'secret')) {
            return;
        }

        DB::table('webhooks')
            ->select(['id', 'secret'])
            ->whereNotNull('secret')
            ->orderBy('id')
            ->chunkById(100, function ($webhooks): void {
                foreach ($webhooks as $webhook) {
                    try {
                        $plain = Crypt::decryptString((string) $webhook->secret);
                    } catch (\Throwable) {
                        // Already-plaintext values can occur after a partial rollback.
                        continue;
                    }

                    DB::table('webhooks')
                        ->where('id', $webhook->id)
                        ->update(['secret' => $plain]);
                }
            });

        Schema::table('webhooks', function (Blueprint $table) {
            $table->string('secret', 120)->nullable()->change();
        });
    }
};
