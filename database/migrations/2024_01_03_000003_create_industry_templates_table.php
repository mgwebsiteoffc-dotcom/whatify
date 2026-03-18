<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industry_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('industry');
            $table->text('description')->nullable();
            $table->string('trigger_type');
            $table->json('trigger_config')->nullable();
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('industry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_templates');
    }
};