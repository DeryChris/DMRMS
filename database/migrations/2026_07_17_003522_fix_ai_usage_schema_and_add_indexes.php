<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to ai_usage so logUsage() works
        Schema::table('ai_usage', function (Blueprint $table) {
            $table->string('feature', 50)->nullable()->after('admin_id');
            $table->unsignedInteger('tokens_used')->nullable()->after('total_tokens');
            $table->decimal('cost', 10, 6)->nullable()->after('total_cost');
            $table->decimal('response_time_ms', 10, 3)->nullable()->after('cost');
            $table->json('metadata')->nullable()->after('response_time_ms');
        });

        // Foreign key indexes for PostgreSQL performance
        Schema::table('cycles', function (Blueprint $table) {
            $table->index('created_by');
        });
        Schema::table('vouchers', function (Blueprint $table) {
            $table->index('cycle_id');
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->index('applicant_id');
            $table->index('cycle_id');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->index('application_id');
        });
        Schema::table('eligibility_results', function (Blueprint $table) {
            $table->index('application_id');
        });
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->index('application_id');
            $table->index('applicant_id');
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->index('application_id');
        });
        Schema::table('screening_results', function (Blueprint $table) {
            $table->index('application_id');
        });
        Schema::table('final_decisions', function (Blueprint $table) {
            $table->index('application_id');
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->foreign('user_id')->references('id')->on('administrators')->onDelete('cascade');
            $table->index('user_id');
            $table->index('created_at');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('applicant_id');
            $table->index('admin_id');
            $table->index('read_at');
            $table->index('type');
        });
        Schema::table('failed_login_attempts', function (Blueprint $table) {
            $table->index('email');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage', function (Blueprint $table) {
            $table->dropColumn(['feature', 'tokens_used', 'cost', 'response_time_ms', 'metadata']);
        });
        Schema::table('cycles', function (Blueprint $table) { $table->dropIndex(['created_by']); });
        Schema::table('vouchers', function (Blueprint $table) { $table->dropIndex(['cycle_id']); });
        Schema::table('applications', function (Blueprint $table) { $table->dropIndex(['applicant_id', 'cycle_id']); });
        Schema::table('documents', function (Blueprint $table) { $table->dropIndex(['application_id']); });
        Schema::table('eligibility_results', function (Blueprint $table) { $table->dropIndex(['application_id']); });
        Schema::table('verification_codes', function (Blueprint $table) { $table->dropIndex(['application_id', 'applicant_id']); });
        Schema::table('appointments', function (Blueprint $table) { $table->dropIndex(['application_id']); });
        Schema::table('screening_results', function (Blueprint $table) { $table->dropIndex(['application_id']); });
        Schema::table('final_decisions', function (Blueprint $table) { $table->dropIndex(['application_id']); });
        Schema::table('audit_logs', function (Blueprint $table) { $table->dropForeign(['user_id']); $table->dropIndex(['user_id', 'created_at']); });
        Schema::table('notifications', function (Blueprint $table) { $table->dropIndex(['applicant_id', 'admin_id', 'read_at', 'type']); });
        Schema::table('failed_login_attempts', function (Blueprint $table) { $table->dropIndex(['email', 'ip_address']); });
    }
};
