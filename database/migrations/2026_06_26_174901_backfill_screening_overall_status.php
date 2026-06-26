<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE screening_results
            SET overall_status = CASE
                WHEN medical_result IN ('unfit', 'fail', 'not_recommended')
                  OR fitness_result IN ('unfit', 'fail', 'not_recommended')
                  OR interview_result IN ('unfit', 'fail', 'not_recommended')
                THEN 'fail'
                WHEN medical_result IN ('fit', 'pass', 'recommended')
                  AND fitness_result IN ('fit', 'pass', 'recommended')
                  AND interview_result IN ('fit', 'pass', 'recommended')
                THEN 'pass'
                WHEN medical_result = 'pending'
                  AND fitness_result = 'pending'
                  AND interview_result = 'pending'
                THEN 'pending'
                ELSE 'in_progress'
            END
        ");
    }

    public function down(): void
    {
        DB::table('screening_results')->update(['overall_status' => 'pending']);
    }
};
