<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'price', 'billing_cycle',
        'whatsapp_numbers', 'automation_flows', 'agents',
        'campaigns_per_month', 'contacts_limit', 'messages_per_month',
        'shared_inbox', 'flow_builder', 'api_access', 'webhook_access',
        'shopify_integration', 'woocommerce_integration',
        'google_sheets_integration', 'custom_integrations',
        'priority_support', 'is_active', 'sort_order', 'features',
    ];

    protected $casts = [
        'features' => 'array',
        'shared_inbox' => 'boolean',
        'flow_builder' => 'boolean',
        'api_access' => 'boolean',
        'webhook_access' => 'boolean',
        'shopify_integration' => 'boolean',
        'woocommerce_integration' => 'boolean',
        'google_sheets_integration' => 'boolean',
        'custom_integrations' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isUnlimited(string $feature): bool
    {
        return ($this->{$feature} ?? 0) === -1;
    }
}