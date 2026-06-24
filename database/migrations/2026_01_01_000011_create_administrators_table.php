<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrators', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('role')->default('admin');
            $table->jsonb('permissions')->nullable();
            $table->string('subscription_tier')->default('basic');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->unsignedInteger('ai_usage_limit')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrators');
    }
};
