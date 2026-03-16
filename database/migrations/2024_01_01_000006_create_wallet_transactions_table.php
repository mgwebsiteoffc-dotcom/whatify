<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit', 'refund', 'bonus']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // message, campaign, recharge
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};