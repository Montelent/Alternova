<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alternative_proprietary_tool')) {
            Schema::create('alternative_proprietary_tool', function (Blueprint $table) {
                $table->id();
                $table->foreignId('open_source_alternative_id')->constrained('open_source_alternatives')->cascadeOnDelete();
                $table->foreignId('proprietary_tool_id')->constrained('proprietary_tools')->cascadeOnDelete();
                $table->unsignedTinyInteger('position')->default(0);
                $table->timestamps();
                $table->unique(['open_source_alternative_id', 'proprietary_tool_id'], 'alt_tool_unique');
                $table->index('proprietary_tool_id', 'alt_tool_prop_idx');
            });

            // Seed pivot from existing primary FK
            try {
                $rows = DB::table('open_source_alternatives')
                    ->whereNotNull('proprietary_tool_id')
                    ->select('id', 'proprietary_tool_id')
                    ->get();
                foreach ($rows as $row) {
                    DB::table('alternative_proprietary_tool')->insertOrIgnore([
                        'open_source_alternative_id' => $row->id,
                        'proprietary_tool_id' => $row->proprietary_tool_id,
                        'position' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable) {
            }
        }

        if (! Schema::hasTable('oss_categories')) {
            Schema::create('oss_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            $defaults = [
                'Analytics', 'Chat & Communication', 'CRM', 'Design', 'Dev Tools',
                'Docs & Knowledge', 'E-commerce', 'Email', 'Finance', 'Forms & Surveys',
                'Hosting & Infra', 'Media & Images', 'Monitoring', 'Notes & Productivity',
                'Project Management', 'Security', 'Storage & Files', 'Video & Meetings',
            ];
            foreach ($defaults as $i => $name) {
                DB::table('oss_categories')->insert([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'sort_order' => ($i + 1) * 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! Schema::hasTable('license_types')) {
            Schema::create('license_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('spdx_id')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_osi_approved')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            $licenses = [
                ['MIT', 'MIT'],
                ['Apache-2.0', 'Apache-2.0'],
                ['AGPL-3.0', 'AGPL-3.0'],
                ['GPL-3.0', 'GPL-3.0'],
                ['BSD-3-Clause', 'BSD-3-Clause'],
                ['MPL-2.0', 'MPL-2.0'],
                ['BSL-1.1', 'BSL-1.1'],
                ['Other', 'other'],
            ];
            foreach ($licenses as $i => [$name, $spdx]) {
                DB::table('license_types')->insert([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'spdx_id' => $spdx,
                    'is_osi_approved' => $name !== 'Other' && $name !== 'BSL-1.1',
                    'is_active' => true,
                    'sort_order' => ($i + 1) * 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_proprietary_tool');
        Schema::dropIfExists('oss_categories');
        Schema::dropIfExists('license_types');
    }
};
