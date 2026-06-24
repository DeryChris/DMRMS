<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_decisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->string('decision');
            $table->text('decision_reason')->nullable();
            $table->jsonb('committee_members')->nullable();
            $table->timestamp('decision_date');
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_decisions');
    }
};
