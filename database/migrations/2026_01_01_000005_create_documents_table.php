<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->string('document_type', 50);
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size');
            $table->string('mime_type', 50);
            $table->timestamp('upload_date');
            $table->string('verification_status')->default('pending');
            $table->unsignedInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('fraud_risk_score')->nullable();
            $table->jsonb('fraud_flags')->nullable();
            $table->boolean('ai_verified')->default(false);
            $table->jsonb('ai_extracted_data')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
