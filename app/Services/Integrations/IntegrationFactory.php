<?php

namespace App\Services\Integrations;

use App\Models\Integration;

class IntegrationFactory
{
    public static function make(string $type): BaseIntegrationService
    {
        return match ($type) {
            'shopify' => new ShopifyService(),
            'woocommerce' => new WooCommerceService(),
            'google_sheets' => new GoogleSheetsService(),
            default => throw new \InvalidArgumentException("Unknown integration type: {$type}"),
        };
    }

    public static function fromIntegration(Integration $integration): BaseIntegrationService
    {
        $service = self::make($integration->type);
        $service->setIntegration($integration);
        return $service;
    }
}