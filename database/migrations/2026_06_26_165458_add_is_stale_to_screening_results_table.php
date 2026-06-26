<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_results', function (Blueprint $table) {
            $table->boolean('is_stale')->default(false)->after('conducted_at');
        });
    }

    public function down(): void
    {
        Schema::table('screening_results', function (Blueprint $table) {
            $table->dropColumn('is_stale');
        });
    }
};
