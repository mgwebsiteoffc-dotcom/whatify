<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->enum('type', ['text', 'image', 'video', 'audio', 'document', 'location', 'contact', 'sticker', 'template', 'interactive', 'reaction', 'order']);
            $table->text('content')->nullable();
            $table->json('media')->nullable(); // {url, mime_type, filename, size}
            $table->json('template_data')->nullable();
            $table->json('interactive_data')->nullable(); // buttons, lists
            $table->json('location_data')->nullable();
            $table->string('wamid')->nullable(); // WhatsApp message ID
            $table->enum('status', ['queued', 'sent', 'delivered', 'read', 'failed', 'received'])->default('queued');
            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->decimal('cost', 8, 4)->default(0);
            $table->enum('message_category', ['marketing', 'utility', 'authentication', 'service'])->nullable();
            $table->boolean('is_bot_response')->default(false);
            $table->string('sent_by')->nullable(); // agent name or 'bot' or 'campaign'
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'direction']);
            $table->index(['conversation_id', 'created_at']);
            $table->index(['contact_id', 'created_at']);
            $table->index(['campaign_id', 'status']);
            $table->index('wamid');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};