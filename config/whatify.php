<?php

return [
    'message_cost' => [
        'marketing' => env('MARKETING_MSG_COST', 0.90),
        'utility' => env('UTILITY_MSG_COST', 0.50),
        'authentication' => env('AUTH_MSG_COST', 0.30),
        'service' => env('SERVICE_MSG_COST', 0.00),
    ],

    'wallet' => [
        'min_recharge' => 500,
        'max_recharge' => 100000,
        'low_balance_alert' => env('LOW_BALANCE_ALERT', 100),
        'currency' => 'INR',
    ],

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price' => 999,
            'whatsapp_numbers' => 1,
            'automation_flows' => 5,
            'agents' => 1,
            'campaigns_per_month' => 10,
            'contacts_limit' => 5000,
        ],
        'growth' => [
            'name' => 'Growth',
            'price' => 2999,
            'whatsapp_numbers' => 3,
            'automation_flows' => 20,
            'agents' => 5,
            'campaigns_per_month' => 50,
            'contacts_limit' => 25000,
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 9999,
            'whatsapp_numbers' => -1, // unlimited
            'automation_flows' => -1,
            'agents' => -1,
            'campaigns_per_month' => -1,
            'contacts_limit' => -1,
        ],
    ],

    'industries' => [
        'ecommerce' => 'E-Commerce',
        'education' => 'Education',
        'healthcare' => 'Healthcare',
        'real_estate' => 'Real Estate',
        'restaurant' => 'Restaurant',
        'travel' => 'Travel & Tourism',
        'saas' => 'SaaS & Technology',
        'agency' => 'Digital Agency',
        'retail' => 'Retail',
        'fitness' => 'Fitness & Gym',
        'salon' => 'Salon & Beauty',
        'other' => 'Other',
    ],

    'whatsapp' => [
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'app_id' => env('WHATSAPP_APP_ID'),
        'app_secret' => env('WHATSAPP_APP_SECRET'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'api_version' => 'v18.0',
    ],

    'partner' => [
        'default_commission' => 20, // percentage
        'min_payout' => 1000,
        'payout_cycle_days' => 30,
    ],

    'upload' => [
        'max_file_size' => 16384, // 16MB in KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'csv', 'mp4', 'mp3'],
    ],
];