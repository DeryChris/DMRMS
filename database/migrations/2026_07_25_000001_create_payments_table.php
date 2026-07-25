<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Link to voucher (nullable — payment may be initiated before voucher fully created)
            $table->foreignId('voucher_id')->nullable()->constrained()->cascadeOnDelete();

            // Paystack identifiers
            $table->string('paystack_reference', 100)->unique();
            $table->string('paystack_access_code', 100)->nullable();

            // Amount & currency
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('GHS');

            // Payment channel
            $table->string('channel', 50);  // 'mobile_money', 'card', 'bank_transfer', 'bank_deposit'
            $table->string('status', 20)->default('pending');
            // Statuses: pending, processing, success, failed, abandoned, reversed

            // Payer info
            $table->string('payer_name', 100);
            $table->string('payer_email', 100);
            $table->string('payer_phone', 20)->nullable();

            // Mobile Money specifics
            $table->string('momo_provider', 20)->nullable();   // 'mtn', 'atl', 'vod'
            $table->string('momo_phone', 20)->nullable();

            // Card specifics (tokenized — never store raw card details)
            $table->string('card_last4', 4)->nullable();
            $table->string('card_brand', 20)->nullable();
            $table->unsignedTinyInteger('card_exp_month')->nullable();
            $table->unsignedSmallInteger('card_exp_year')->nullable();
            $table->string('authorization_code', 100)->nullable();

            // Bank Transfer specifics
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->timestamp('bank_transfer_deadline')->nullable();

            // Paystack response data
            $table->string('paystack_status', 50)->nullable();
            $table->string('gateway_response', 255)->nullable();
            $table->json('paystack_response')->nullable();

            // Fees & metadata
            $table->decimal('fees', 10, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Index for fast lookups
            $table->index('status');
            $table->index('payer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
