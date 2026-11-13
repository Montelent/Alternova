<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        // New accounts must be ordinary members unless an authorized admin
        // explicitly assigns a privileged role.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('member')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('admin')->change();
        });
    }
};
