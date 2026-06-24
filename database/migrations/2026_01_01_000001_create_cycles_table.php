<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('cycle_code', 20)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->dateTime('application_deadline');
            $table->unsignedInteger('total_vacancies');
            $table->jsonb('requirements')->nullable();
            $table->boolean('ai_enabled')->default(false);
            $table->string('status')->default('draft');
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
