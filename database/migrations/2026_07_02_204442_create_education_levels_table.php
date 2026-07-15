<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('rank');
            $table->integer('min_age')->default(18);
            $table->integer('max_age')->default(40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_levels');
    }
};
