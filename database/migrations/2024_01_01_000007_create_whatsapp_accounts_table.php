<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('phone_number', 20);
            $table->string('phone_number_id')->nullable(); // Meta phone number ID
            $table->string('waba_id')->nullable(); // WhatsApp Business Account ID
            $table->string('business_id_meta')->nullable(); // Meta Business ID
            $table->string('display_name')->nullable();
            $table->text('access_token')->nullable();
            $table->enum('quality_rating', ['GREEN', 'YELLOW', 'RED', 'UNKNOWN'])->default('UNKNOWN');
            $table->enum('status', ['pending', 'verified', 'connected', 'disconnected', 'banned'])->default('pending');
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_accounts');
    }
};