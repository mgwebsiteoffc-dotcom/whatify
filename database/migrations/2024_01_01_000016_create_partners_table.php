<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name');
            $table->enum('type', ['agency', 'reseller', 'influencer', 'technology'])->default('reseller');
            $table->string('referral_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(20.00); // percentage
            $table->decimal('total_earned', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('pending_payout', 12, 2)->default(0);
            $table->integer('total_referrals')->default(0);
            $table->integer('active_customers')->default(0);
            $table->json('payout_details')->nullable(); // bank details
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('partner_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->json('payout_details')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('partner_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // referred customer
            $table->string('event'); // subscription, recharge
            $table->decimal('amount', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_commissions');
        Schema::dropIfExists('partner_payouts');
        Schema::dropIfExists('partners');
    }
};