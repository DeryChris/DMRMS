<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administrators', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('administrators', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};
