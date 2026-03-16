<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', ['super_admin', 'business_owner', 'team_agent', 'partner'])->default('business_owner')->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending')->after('role');
            $table->string('avatar')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->boolean('is_onboarded')->default(false);
            $table->integer('onboarding_step')->default(0);
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'role', 'status', 'avatar', 'timezone',
                'last_login_at', 'last_login_ip', 'is_onboarded',
                'onboarding_step', 'partner_id'
            ]);
            $table->dropSoftDeletes();
        });
    }
};