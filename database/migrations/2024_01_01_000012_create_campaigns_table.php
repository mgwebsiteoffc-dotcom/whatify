<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('message_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'processing', 'sending', 'paused', 'completed', 'failed', 'cancelled'])->default('draft');
            $table->json('audience_filter')->nullable(); // tag filters, segments
            $table->json('template_variables')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_contacts')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('replied_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->integer('messages_per_second')->default(30);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        Schema::create('campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
           $table->unsignedBigInteger('message_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'replied', 'failed'])->default('pending');
            $table->string('error_message')->nullable();
            $table->json('variables')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_contacts');
        Schema::dropIfExists('campaigns');
    }
};