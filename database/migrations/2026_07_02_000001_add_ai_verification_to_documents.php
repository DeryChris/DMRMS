<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->float('ai_confidence')->nullable()->after('verification_status');
            $table->json('extracted_data')->nullable()->after('ai_confidence');
            $table->json('cross_reference_results')->nullable()->after('extracted_data');
            $table->timestamp('ai_verified_at')->nullable()->after('cross_reference_results');
            $table->unsignedTinyInteger('ai_verification_attempts')->default(0)->after('ai_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'ai_confidence',
                'extracted_data',
                'cross_reference_results',
                'ai_verified_at',
                'ai_verification_attempts',
            ]);
        });
    }
};
