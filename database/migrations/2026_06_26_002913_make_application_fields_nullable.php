<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('education_level', 50)->nullable()->change();
            $table->string('institution_name', 200)->nullable()->change();
            $table->string('qualification', 100)->nullable()->change();
            $table->string('year_obtained', 4)->nullable()->change();
            $table->decimal('height', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('education_level', 50)->nullable(false)->change();
            $table->string('institution_name', 200)->nullable(false)->change();
            $table->string('qualification', 100)->nullable(false)->change();
            $table->string('year_obtained', 4)->nullable(false)->change();
            $table->decimal('height', 5, 2)->nullable(false)->change();
        });
    }
};
