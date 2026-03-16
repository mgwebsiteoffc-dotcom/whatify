<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('total_recharged', 12, 2)->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->string('currency', 5)->default('INR');
            $table->boolean('auto_recharge')->default(false);
            $table->decimal('auto_recharge_amount', 10, 2)->nullable();
            $table->decimal('auto_recharge_threshold', 10, 2)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};