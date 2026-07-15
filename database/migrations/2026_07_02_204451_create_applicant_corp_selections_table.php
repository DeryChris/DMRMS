<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_corp_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corp_id')->constrained()->cascadeOnDelete();
            $table->integer('priority');
            $table->timestamps();

            $table->unique(['application_id', 'corp_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_corp_selections');
    }
};
