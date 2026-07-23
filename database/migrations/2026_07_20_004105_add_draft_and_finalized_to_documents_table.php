<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('is_draft')->default(true)->after('verification_status');
            $table->timestamp('finalized_at')->nullable()->after('ai_verified_at');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('documents_finalized')->default(false)->after('submitted_at');
            $table->timestamp('documents_finalized_at')->nullable()->after('documents_finalized');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['is_draft', 'finalized_at']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['documents_finalized', 'documents_finalized_at']);
        });
    }
};
