<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('cycle_id');
            $table->string('serial_number', 20)->unique();
            $table->string('pin_code', 20);
            $table->timestamp('purchased_at')->nullable();
            $table->unsignedInteger('used_by')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->string('status')->default('available');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('cycle_id')->references('id')->on('cycles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
