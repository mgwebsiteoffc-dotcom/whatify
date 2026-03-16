<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@whatify.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@whatify.com',
                'phone' => '9999999999',
                'password' => Hash::make('Admin@123'),
                'role' => 'super_admin',
                'status' => 'active',
                'is_onboarded' => true,
                'onboarding_step' => 4,
                'email_verified_at' => now(),
            ]
        );

        Wallet::firstOrCreate(
            ['user_id' => $admin->id],
            ['balance' => 0, 'currency' => 'INR']
        );
    }
}