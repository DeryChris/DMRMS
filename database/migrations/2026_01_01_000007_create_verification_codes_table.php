<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('application_id');
            $table->string('code_value', 20)->unique();
            $table->string('qr_code_path', 255)->nullable();
            $table->timestamp('issue_date');
            $table->timestamp('expiry_date');
            $table->boolean('used_status')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
