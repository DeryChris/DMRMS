<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('corps', function (Blueprint $table) {
            $table->integer('max_capacity')->nullable()->after('is_active');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('allocated_corp_id')->nullable()->after('ai_verified_at')
                ->constrained('corps')->nullOnDelete();
            $table->string('allocation_status', 20)->default('pending')
                ->after('allocated_corp_id');
            $table->index('allocation_status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['allocation_status']);
            $table->dropColumn('allocation_status');
            $table->dropForeign(['allocated_corp_id']);
            $table->dropColumn('allocated_corp_id');
        });

        Schema::table('corps', function (Blueprint $table) {
            $table->dropColumn('max_capacity');
        });
    }
};
