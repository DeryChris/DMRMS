<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('final_decisions', function (Blueprint $table) {
            if (!Schema::hasColumn('final_decisions', 'reporting_code')) {
                $table->string('reporting_code', 20)->unique()->nullable()->after('decision_date');
            }
            if (!Schema::hasColumn('final_decisions', 'barrack_id')) {
                $table->foreignId('barrack_id')->nullable()->constrained('barracks')->nullOnDelete()->after('reporting_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_decisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barrack_id');
            $table->dropColumn('reporting_code');
        });
    }
};
