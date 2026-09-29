<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSeoColumns('open_source_alternatives');
        $this->addSeoColumns('proprietary_tools');
    }

    protected function addSeoColumns(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $columns = [
            'focus_keyword' => fn (Blueprint $t) => $t->string('focus_keyword', 120)->nullable(),
            'robots_meta' => fn (Blueprint $t) => $t->string('robots_meta', 80)->nullable(),
            'canonical_url' => fn (Blueprint $t) => $t->string('canonical_url', 500)->nullable(),
            'og_title' => fn (Blueprint $t) => $t->string('og_title', 120)->nullable(),
            'og_description' => fn (Blueprint $t) => $t->string('og_description', 200)->nullable(),
            'og_image_url' => fn (Blueprint $t) => $t->string('og_image_url', 500)->nullable(),
        ];

        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($definition) {
                $definition($blueprint);
            });
        }
    }

    public function down(): void
    {
        $cols = ['focus_keyword', 'robots_meta', 'canonical_url', 'og_title', 'og_description', 'og_image_url'];

        foreach (['open_source_alternatives', 'proprietary_tools'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($cols as $col) {
                if (Schema::hasColumn($table, $col)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($col) {
                        $blueprint->dropColumn($col);
                    });
                }
            }
        }
    }
};
