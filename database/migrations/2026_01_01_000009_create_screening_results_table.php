<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->string('medical_result')->default('pending');
            $table->text('medical_notes')->nullable();
            $table->string('fitness_result')->default('pending');
            $table->unsignedInteger('fitness_score')->nullable();
            $table->string('interview_result')->default('pending');
            $table->text('interview_notes')->nullable();
            $table->string('overall_status')->default('pending');
            $table->unsignedInteger('conducted_by')->nullable();
            $table->timestamp('conducted_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_results');
    }
};
