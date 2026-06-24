<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_predictions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('cycle_id');
            $table->string('prediction_type', 50);
            $table->jsonb('predicted_value');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamp('generated_at');

            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
        });

        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('applicant_id')->nullable();
            $table->string('session_id', 100);
            $table->text('question');
            $table->text('answer');
            $table->string('ai_model', 50)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->timestamp('created_at');

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
        });

        Schema::create('ai_prompt_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('user_type', 20)->nullable();
            $table->string('prompt_type', 50);
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->string('model', 50)->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->decimal('processing_time', 10, 3)->nullable();
            $table->string('status')->default('success');
            $table->timestamp('created_at');
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->text('system_prompt');
            $table->text('user_prompt_template');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_usage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('admin_id');
            $table->date('date');
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('total_cost', 10, 4)->default(0);
            $table->unsignedInteger('requests_count')->default(0);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('administrators')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_prompt_logs');
        Schema::dropIfExists('chatbot_conversations');
        Schema::dropIfExists('ai_predictions');
    }
};
