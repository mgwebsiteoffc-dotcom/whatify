<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Services\Integrations\IntegrationFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IntegrationWebhookController extends Controller
{
    public function shopify(Request $request, int $integrationId)
    {
        $integration = Integration::where('id', $integrationId)->where('type', 'shopify')->first();

        if (!$integration || $integration->status !== 'active') {
            return response()->json(['error' => 'Integration not found'], 404);
        }

        $hmacHeader = $request->header('X-Shopify-Hmac-SHA256');
        $topic = $request->header('X-Shopify-Topic');

        if (!$topic) {
            return response()->json(['error' => 'Missing topic'], 400);
        }

        Log::channel('whatsapp')->info('Shopify webhook received', [
            'integration_id' => $integrationId,
            'topic' => $topic,
        ]);

        try {
            $service = IntegrationFactory::fromIntegration($integration);
            $service->handleWebhook($topic, $request->all());
        } catch (\Exception $e) {
            Log::error('Shopify webhook processing failed', [
                'integration_id' => $integrationId,
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function woocommerce(Request $request, int $integrationId)
    {
        $integration = Integration::where('id', $integrationId)->where('type', 'woocommerce')->first();

        if (!$integration || $integration->status !== 'active') {
            return response()->json(['error' => 'Integration not found'], 404);
        }

        $topic = $request->header('X-WC-Webhook-Topic');

        if (!$topic) {
            $topic = $request->header('X-Wc-Webhook-Topic', 'unknown');
        }

        Log::channel('whatsapp')->info('WooCommerce webhook received', [
            'integration_id' => $integrationId,
            'topic' => $topic,
        ]);

        try {
            $service = IntegrationFactory::fromIntegration($integration);
            $service->handleWebhook($topic, $request->all());
        } catch (\Exception $e) {
            Log::error('WooCommerce webhook processing failed', [
                'integration_id' => $integrationId,
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}