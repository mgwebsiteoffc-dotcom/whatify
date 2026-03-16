<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('template_id_meta')->nullable(); // Meta template ID
            $table->enum('category', ['marketing', 'utility', 'authentication']);
            $table->string('language', 10)->default('en');
            $table->enum('status', ['pending', 'approved', 'rejected', 'paused', 'disabled'])->default('pending');
            $table->json('header')->nullable(); // {type: text/image/video/document, content: ...}
            $table->text('body');
            $table->text('footer')->nullable();
            $table->json('buttons')->nullable(); // [{type: quick_reply/url/phone, text: ..., ...}]
            $table->json('variables')->nullable(); // [{index: 1, sample: "John"}]
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};