<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 999,
                'billing_cycle' => 'monthly',
                'whatsapp_numbers' => 1,
                'automation_flows' => 5,
                'agents' => 1,
                'campaigns_per_month' => 10,
                'contacts_limit' => 5000,
                'messages_per_month' => -1,
                'shared_inbox' => true,
                'flow_builder' => false,
                'api_access' => false,
                'webhook_access' => false,
                'shopify_integration' => false,
                'woocommerce_integration' => false,
                'google_sheets_integration' => true,
                'custom_integrations' => false,
                'priority_support' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'price' => 2999,
                'billing_cycle' => 'monthly',
                'whatsapp_numbers' => 3,
                'automation_flows' => 20,
                'agents' => 5,
                'campaigns_per_month' => 50,
                'contacts_limit' => 25000,
                'messages_per_month' => -1,
                'shared_inbox' => true,
                'flow_builder' => true,
                'api_access' => true,
                'webhook_access' => true,
                'shopify_integration' => true,
                'woocommerce_integration' => true,
                'google_sheets_integration' => true,
                'custom_integrations' => false,
                'priority_support' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'price' => 9999,
                'billing_cycle' => 'monthly',
                'whatsapp_numbers' => -1,
                'automation_flows' => -1,
                'agents' => -1,
                'campaigns_per_month' => -1,
                'contacts_limit' => -1,
                'messages_per_month' => -1,
                'shared_inbox' => true,
                'flow_builder' => true,
                'api_access' => true,
                'webhook_access' => true,
                'shopify_integration' => true,
                'woocommerce_integration' => true,
                'google_sheets_integration' => true,
                'custom_integrations' => true,
                'priority_support' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}