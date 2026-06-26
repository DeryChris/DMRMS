<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('returned_count')->default(0)->after('ai_verified_at');
            $table->string('last_returned_from')->nullable()->after('returned_count');
            $table->string('last_returned_to')->nullable()->after('last_returned_from');
            $table->text('last_return_reason')->nullable()->after('last_returned_to');
            $table->timestamp('last_returned_at')->nullable()->after('last_return_reason');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['returned_count', 'last_returned_from', 'last_returned_to', 'last_return_reason', 'last_returned_at']);
        });
    }
};
