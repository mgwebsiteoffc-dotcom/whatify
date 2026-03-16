<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->integer('whatsapp_numbers')->default(1);  // -1 = unlimited
            $table->integer('automation_flows')->default(5);
            $table->integer('agents')->default(1);
            $table->integer('campaigns_per_month')->default(10);
            $table->integer('contacts_limit')->default(5000);
            $table->integer('messages_per_month')->default(-1);
            $table->boolean('shared_inbox')->default(true);
            $table->boolean('flow_builder')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('webhook_access')->default(false);
            $table->boolean('shopify_integration')->default(false);
            $table->boolean('woocommerce_integration')->default(false);
            $table->boolean('google_sheets_integration')->default(false);
            $table->boolean('custom_integrations')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('features')->nullable(); // extra feature flags
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};