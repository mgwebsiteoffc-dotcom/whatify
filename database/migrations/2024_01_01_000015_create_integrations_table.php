<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['shopify', 'woocommerce', 'magento', 'google_sheets', 'slack', 'webhook', 'zapier', 'pabbly']);
            $table->string('name');
            $table->json('config')->nullable(); // encrypted credentials
            $table->enum('status', ['active', 'inactive', 'error'])->default('active');
            $table->timestamp('last_synced_at')->nullable();
            $table->string('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
        });

        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->string('error_message')->nullable();
            $table->timestamps();

            $table->index(['integration_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
        Schema::dropIfExists('integrations');
    }
};