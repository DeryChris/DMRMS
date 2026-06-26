<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('final_decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('final_decisions', 'evaluation')) {
                $table->json('evaluation')->nullable()->after('decision_reason');
            }
            if (!Schema::hasColumn('final_decisions', 'committee_approved_at')) {
                $table->timestamp('committee_approved_at')->nullable()->after('evaluation');
            }
            if (!Schema::hasColumn('final_decisions', 'committee_approved_by')) {
                $table->unsignedInteger('committee_approved_by')->nullable()->after('committee_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('final_decisions', function (Blueprint $table) {
            $table->dropColumn(['evaluation', 'committee_approved_at', 'committee_approved_by']);
        });
    }
};
