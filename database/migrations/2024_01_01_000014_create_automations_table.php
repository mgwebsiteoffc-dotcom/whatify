<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', [
                'incoming_message', 'keyword', 'contact_created', 'tag_added',
                'shopify_order', 'shopify_abandoned_cart', 'woocommerce_order',
                'campaign_reply', 'form_submission', 'api_trigger', 'schedule'
            ]);
            $table->json('trigger_config')->nullable(); // keywords, conditions
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->integer('execution_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('flow_data')->nullable(); // full flow builder JSON
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'trigger_type']);
        });

        Schema::create('automation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->string('step_id')->nullable(); // UUID for frontend reference
            $table->enum('type', [
                'send_message', 'send_template', 'send_media',
                'ask_question', 'buttons', 'list_menu',
                'condition', 'delay', 'set_variable',
                'add_tag', 'remove_tag', 'assign_agent',
                'transfer_to_agent', 'api_call', 'webhook',
                'goto_step', 'end_flow', 'send_email',
                'update_contact', 'create_deal'
            ]);
            $table->json('config')->nullable();
            $table->string('next_step_id')->nullable();
            $table->json('branches')->nullable(); // for condition blocks: [{condition, next_step_id}]
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('automation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('current_step_id')->nullable();
            $table->enum('status', ['running', 'completed', 'failed', 'paused'])->default('running');
            $table->json('variables')->nullable(); // collected data
            $table->json('execution_path')->nullable(); // steps executed
            $table->string('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['automation_id', 'contact_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_logs');
        Schema::dropIfExists('automation_steps');
        Schema::dropIfExists('automations');
    }
};