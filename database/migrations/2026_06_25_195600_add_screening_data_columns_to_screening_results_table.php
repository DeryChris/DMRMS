<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('screening_results', function (Blueprint $table) {
            if (!Schema::hasColumn('screening_results', 'medical_status')) {
                $table->string('medical_status', 20)->default('pending')->after('application_id');
            }
            if (!Schema::hasColumn('screening_results', 'medical_data')) {
                $table->json('medical_data')->nullable()->after('medical_notes');
            }
            if (!Schema::hasColumn('screening_results', 'fitness_details')) {
                $table->json('fitness_details')->nullable()->after('fitness_score');
            }
            if (!Schema::hasColumn('screening_results', 'fitness_notes')) {
                $table->text('fitness_notes')->nullable()->after('fitness_details');
            }
            if (!Schema::hasColumn('screening_results', 'interview_score')) {
                $table->unsignedInteger('interview_score')->nullable()->after('interview_notes');
            }
            if (!Schema::hasColumn('screening_results', 'interview_decision')) {
                $table->string('interview_decision', 20)->default('pending')->after('interview_score');
            }
            if (!Schema::hasColumn('screening_results', 'interview_data')) {
                $table->json('interview_data')->nullable()->after('interview_decision');
            }
            if (!Schema::hasColumn('screening_results', 'screened_by')) {
                $table->unsignedInteger('screened_by')->nullable()->after('conducted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('screening_results', function (Blueprint $table) {
            $table->dropColumn([
                'medical_status', 'medical_data', 'fitness_details', 'fitness_notes',
                'interview_score', 'interview_decision', 'interview_data', 'screened_by',
            ]);
        });
    }
};
