<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('collections')) {
            Schema::create('collections', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('intro_html')->nullable();
                $table->string('cover_image_url')->nullable();
                $table->boolean('is_published')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('meta_title')->nullable();
                $table->string('meta_description', 500)->nullable();
                $table->string('focus_keyword')->nullable();
                $table->timestamps();

                $table->index(['is_published', 'is_featured'], 'coll_pub_feat_idx');
            });
        }

        if (! Schema::hasTable('collection_items')) {
            Schema::create('collection_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
                $table->foreignId('open_source_alternative_id')->constrained('open_source_alternatives')->cascadeOnDelete();
                $table->unsignedInteger('position')->default(0);
                $table->string('note', 500)->nullable();
                $table->timestamps();

                $table->unique(['collection_id', 'open_source_alternative_id'], 'coll_item_uq');
                $table->index(['collection_id', 'position'], 'coll_pos_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_items');
        Schema::dropIfExists('collections');
    }
};
