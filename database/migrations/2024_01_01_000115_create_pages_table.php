<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pages')) {
            return;
        }

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('template', 40)->default('default'); // default|legal
            $table->longText('body_html')->nullable();
            $table->string('excerpt', 300)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('show_in_footer')->default(false);
            $table->boolean('show_in_nav')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('robots_meta', 80)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order'], 'pages_pub_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
