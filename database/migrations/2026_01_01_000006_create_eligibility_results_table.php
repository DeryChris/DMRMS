<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eligibility_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->boolean('age_check')->nullable();
            $table->boolean('nationality_check')->nullable();
            $table->boolean('education_check')->nullable();
            $table->boolean('height_check')->nullable();
            $table->boolean('criminal_check')->nullable();
            $table->boolean('document_check')->nullable();
            $table->boolean('marital_check')->nullable();
            $table->string('overall_status');
            $table->jsonb('rejection_reasons')->nullable();
            $table->decimal('ai_confidence', 5, 2)->nullable();
            $table->text('ai_explanation')->nullable();
            $table->timestamp('evaluation_date');
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eligibility_results');
    }
};
