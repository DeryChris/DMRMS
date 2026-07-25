<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedInteger('cycle_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Ensure no null cycle_ids exist before reverting
        DB::table('applications')->whereNull('cycle_id')->update(['cycle_id' => 0]);

        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedInteger('cycle_id')->nullable(false)->change();
        });
    }
};
