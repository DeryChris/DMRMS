<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('voucher_id')->unique()->nullable();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('other_names', 50)->nullable();
            $table->date('date_of_birth');
            $table->string('gender', 10);
            $table->string('marital_status', 20)->default('Single');
            $table->string('contact_number', 15);
            $table->string('alternative_contact', 15)->nullable();
            $table->string('email', 100)->unique();
            $table->text('residential_address');
            $table->string('region', 50);
            $table->string('district', 50);
            $table->string('nationality', 50)->default('Ghanaian');
            $table->string('national_id', 20);
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('set null');
            $table->index('contact_number');
            $table->index('region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicants');
    }
};
