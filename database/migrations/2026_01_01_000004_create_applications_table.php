<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('applicant_id');
            $table->unsignedInteger('cycle_id');
            $table->string('gaf_id', 20)->unique();
            $table->timestamp('application_date');
            $table->string('education_level', 50);
            $table->string('institution_name', 200);
            $table->string('qualification', 100);
            $table->string('year_obtained', 4);
            $table->string('certificate_number', 50)->nullable();
            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2)->nullable();
            $table->jsonb('health_conditions')->nullable();
            $table->boolean('criminal_record')->default(false);
            $table->string('fitness_status', 20)->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('ai_eligibility_score', 5, 2)->nullable();
            $table->decimal('ai_ranking_score', 5, 2)->nullable();
            $table->timestamp('ai_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
