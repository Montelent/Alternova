<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collections')) {
            return;
        }

        $columns = [
            'robots_meta' => fn (Blueprint $t) => $t->string('robots_meta', 80)->nullable()->after('focus_keyword'),
            'canonical_url' => fn (Blueprint $t) => $t->string('canonical_url', 500)->nullable()->after('robots_meta'),
            'og_title' => fn (Blueprint $t) => $t->string('og_title', 120)->nullable()->after('canonical_url'),
            'og_description' => fn (Blueprint $t) => $t->string('og_description', 200)->nullable()->after('og_title'),
            'og_image_url' => fn (Blueprint $t) => $t->string('og_image_url', 500)->nullable()->after('og_description'),
        ];

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn('collections', $name)) {
                continue;
            }
            Schema::table('collections', function (Blueprint $blueprint) use ($definition) {
                $definition($blueprint);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('collections')) {
            return;
        }
        foreach (['robots_meta', 'canonical_url', 'og_title', 'og_description', 'og_image_url'] as $col) {
            if (Schema::hasColumn('collections', $col)) {
                Schema::table('collections', function (Blueprint $blueprint) use ($col) {
                    $blueprint->dropColumn($col);
                });
            }
        }
    }
};
