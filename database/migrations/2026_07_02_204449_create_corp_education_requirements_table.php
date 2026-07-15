<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corp_education_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corp_id')->constrained()->cascadeOnDelete();
            $table->foreignId('education_level_id')->constrained()->cascadeOnDelete();
            $table->string('degree_field_group');
            $table->json('specific_degrees')->nullable();
            $table->json('additional_certs')->nullable();
            $table->string('min_grade')->nullable();
            $table->timestamps();

            $table->unique(['corp_id', 'education_level_id', 'degree_field_group']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corp_education_requirements');
    }
};
