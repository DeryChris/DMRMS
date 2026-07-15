<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->dropUnique('applicants_email_unique');
        });

        Schema::table('applicants', function (Blueprint $table) {
            $table->index('email', 'applicants_email_index');
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->dropUnique('administrators_email_unique');
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->index('email', 'administrators_email_index');
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->dropUnique('administrators_username_unique');
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->index('username', 'administrators_username_index');
        });
    }

    public function down(): void
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('applicants_email_index');
            $table->unique('email', 'applicants_email_unique');
        });

        Schema::table('administrators', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex('administrators_email_index');
            $table->unique('email', 'administrators_email_unique');
            $table->dropIndex('administrators_username_index');
            $table->unique('username', 'administrators_username_unique');
        });
    }
};
