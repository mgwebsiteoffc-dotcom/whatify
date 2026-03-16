<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // WhatsApp Interactive Forms
        Schema::create('whatsapp_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('flow_id_meta')->nullable();
            $table->json('screens')->nullable();
            $table->enum('status', ['draft', 'published', 'deprecated'])->default('draft');
            $table->timestamps();
        });

        Schema::create('whatsapp_flow_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_flow_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->json('response_data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_flow_responses');
        Schema::dropIfExists('whatsapp_flows');
    }
};