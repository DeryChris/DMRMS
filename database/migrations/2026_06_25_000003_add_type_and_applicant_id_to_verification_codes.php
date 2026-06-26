<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->string('type', 50)->default('entry')->after('code_value');
            $table->unsignedBigInteger('applicant_id')->nullable()->after('application_id');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropForeign(['applicant_id']);
            $table->dropColumn(['type', 'applicant_id']);
        });
    }
};
