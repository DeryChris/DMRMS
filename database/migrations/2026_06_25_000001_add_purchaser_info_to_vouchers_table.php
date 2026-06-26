<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->string('purchaser_name', 100)->nullable()->after('pin_code');
            $table->string('purchaser_email', 100)->nullable()->after('purchaser_name');
            $table->string('purchaser_phone', 20)->nullable()->after('purchaser_email');
            $table->string('payment_method', 50)->nullable()->after('purchaser_phone');
            $table->string('payment_reference', 100)->nullable()->after('payment_method');
            $table->string('payment_status', 20)->default('completed')->after('payment_reference');
            $table->decimal('cost', 10, 2)->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn([
                'purchaser_name',
                'purchaser_email',
                'purchaser_phone',
                'payment_method',
                'payment_reference',
                'payment_status',
                'cost',
            ]);
        });
    }
};
