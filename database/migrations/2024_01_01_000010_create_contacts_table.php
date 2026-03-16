<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code', 5)->default('91');
            $table->enum('status', ['active', 'inactive', 'blocked', 'opted_out'])->default('active');
            $table->string('source')->nullable(); // manual, import, shopify, form, chatbot
            $table->json('custom_attributes')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('opted_in_at')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'phone']);
            $table->index(['user_id', 'status']);
            $table->index('phone');
        });

        // Pivot table for contact tags
        Schema::create('contact_tag', function (Blueprint $table) {
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['contact_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('contacts');
    }
};