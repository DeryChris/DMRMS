<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserve_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id')->unique();
            $table->unsignedInteger('priority_score')->default(0);
            $table->unsignedInteger('position');
            $table->text('notes')->nullable();
            $table->timestamp('promoted_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserve_lists');
    }
};
