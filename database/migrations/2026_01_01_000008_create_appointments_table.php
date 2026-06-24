<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->string('venue', 200);
            $table->unsignedInteger('slot_number');
            $table->string('status')->default('scheduled');
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
