<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('issue_reports')) {
            Schema::create('issue_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('open_source_alternative_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type', 40); // broken_link, wrong_info, spam, other
                $table->string('email', 190)->nullable();
                $table->string('page_url', 500)->nullable();
                $table->text('message');
                $table->string('status', 32)->default('open'); // open, resolved, dismissed
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_reports');
    }
};
