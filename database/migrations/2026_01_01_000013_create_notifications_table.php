<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('applicant_id')->nullable();
            $table->unsignedInteger('admin_id')->nullable();
            $table->string('type', 50);
            $table->string('subject', 255);
            $table->text('message');
            $table->string('channel', 20);
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();

            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('administrators')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
