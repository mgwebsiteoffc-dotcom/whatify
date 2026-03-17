<?php

namespace App\Services\Integrations;

use App\Models\Contact;
use App\Models\Tag;
use App\Jobs\TriggerAutomation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService extends BaseIntegrationService
{
    protected string $apiVersion = '2024-01';

    public function getType(): string
    {
        return 'shopify';
    }

    public function connect(array $config): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $config['access_token'],
            ])->get("https://{$config['shop_domain']}/admin/api/{$this->apiVersion}/shop.json");

            if ($response->successful()) {
                $shopData = $response->json('shop');

                $this->integration->update([
                    'config' => [
                        'shop_domain' => $config['shop_domain'],
                        'access_token' => $config['access_token'],
                        'shop_name' => $shopData['name'] ?? $config['shop_domain'],
                        'shop_email' => $shopData['email'] ?? null,
                        'currency' => $shopData['currency'] ?? 'INR',
                        'webhooks_registered' => false,
                        'sync_orders' => $config['sync_orders'] ?? true,
                        'sync_customers' => $config['sync_customers'] ?? true,
                        'abandoned_cart' => $config['abandoned_cart'] ?? true,
                        'order_confirmation' => $config['order_confirmation'] ?? true,
                        'shipping_updates' => $config['shipping_updates'] ?? true,
                        'cod_verification' => $config['cod_verification'] ?? false,
                    ],
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]);

                $this->registerWebhooks();
                $this->logEvent('connected', ['shop' => $config['shop_domain']]);

                return true;
            }

            $this->logEvent('connection_failed', $config, $response->json(), 'failed', 'Invalid credentials');
            return false;

        } catch (\Exception $e) {
            $this->logEvent('connection_error', $config, null, 'failed', $e->getMessage());
            return false;
        }
    }

    public function disconnect(): bool
    {
        $this->removeWebhooks();
        $this->updateStatus('inactive');
        $this->logEvent('disconnected');
        return true;
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $this->getConfig('access_token'),
            ])->get("https://{$this->getConfig('shop_domain')}/admin/api/{$this->apiVersion}/shop.json");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function registerWebhooks(): void
    {
        $webhookUrl = url("/api/integrations/shopify/webhook/{$this->integration->id}");

        $topics = [
            'orders/create',
            'orders/fulfilled',
            'orders/cancelled',
            'checkouts/create',
            'checkouts/update',
            'customers/create',
            'customers/update',
        ];

        foreach ($topics as $topic) {
            try {
                $response = $this->apiRequest('POST', 'webhooks.json', [
                    'webhook' => [
                        'topic' => $topic,
                        'address' => $webhookUrl,
                        'format' => 'json',
                    ],
                ]);

                if ($response->successful()) {
                    Log::info("Shopify webhook registered: {$topic}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to register Shopify webhook: {$topic}", ['error' => $e->getMessage()]);
            }
        }

        $this->updateConfig(['webhooks_registered' => true]);
    }

    public function removeWebhooks(): void
    {
        try {
            $response = $this->apiRequest('GET', 'webhooks.json');

            if ($response->successful()) {
                $webhooks = $response->json('webhooks', []);
                foreach ($webhooks as $webhook) {
                    $this->apiRequest('DELETE', "webhooks/{$webhook['id']}.json");
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to remove Shopify webhooks', ['error' => $e->getMessage()]);
        }

        $this->updateConfig(['webhooks_registered' => false]);
    }

    public function handleWebhook(string $topic, array $data): void
    {
        $this->logEvent("webhook_{$topic}", $data);

        match ($topic) {
            'orders/create' => $this->handleOrderCreated($data),
            'orders/fulfilled' => $this->handleOrderFulfilled($data),
            'orders/cancelled' => $this->handleOrderCancelled($data),
            'checkouts/create', 'checkouts/update' => $this->handleCheckout($data),
            'customers/create' => $this->handleCustomerCreated($data),
            'customers/update' => $this->handleCustomerUpdated($data),
            default => Log::info("Unhandled Shopify webhook: {$topic}"),
        };
    }

    protected function handleOrderCreated(array $data): void
    {
        $phone = $this->extractPhone($data);
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);

        $orderTag = Tag::firstOrCreate(
            ['user_id' => $this->user->id, 'name' => 'shopify_customer'],
            ['color' => '#8B5CF6']
        );
        $contact->tags()->syncWithoutDetaching([$orderTag->id]);

        $contact->update([
            'custom_attributes' => array_merge($contact->custom_attributes ?? [], [
                'last_order_id' => $data['id'] ?? null,
                'last_order_number' => $data['order_number'] ?? $data['name'] ?? null,
                'last_order_amount' => $data['total_price'] ?? null,
                'last_order_date' => now()->toDateString(),
                'total_orders' => ($contact->custom_attributes['total_orders'] ?? 0) + 1,
                'payment_method' => $data['gateway'] ?? null,
            ]),
        ]);

        $isCOD = strtolower($data['gateway'] ?? '') === 'cash on delivery' ||
                 strtolower($data['payment_gateway_names'][0] ?? '') === 'cod';

        TriggerAutomation::dispatch(
            $this->user->id,
            'shopify_order',
            [
                'contact_id' => $contact->id,
                'order_id' => $data['id'] ?? null,
                'order_number' => $data['order_number'] ?? $data['name'] ?? null,
                'total_price' => $data['total_price'] ?? 0,
                'currency' => $data['currency'] ?? 'INR',
                'is_cod' => $isCOD,
                'items_count' => count($data['line_items'] ?? []),
                'content' => 'shopify_order_created',
            ]
        )->onQueue('automations');
    }

    protected function handleOrderFulfilled(array $data): void
    {
        $phone = $this->extractPhone($data);
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);

        $fulfillments = $data['fulfillments'] ?? [];
        $trackingNumber = $fulfillments[0]['tracking_number'] ?? null;
        $trackingUrl = $fulfillments[0]['tracking_url'] ?? null;
        $trackingCompany = $fulfillments[0]['tracking_company'] ?? null;

        $contact->update([
            'custom_attributes' => array_merge($contact->custom_attributes ?? [], [
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
                'tracking_company' => $trackingCompany,
            ]),
        ]);

        TriggerAutomation::dispatch(
            $this->user->id,
            'shopify_order',
            [
                'contact_id' => $contact->id,
                'order_id' => $data['id'] ?? null,
                'order_number' => $data['order_number'] ?? $data['name'] ?? null,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
                'content' => 'shopify_order_fulfilled',
            ]
        )->onQueue('automations');
    }

    protected function handleOrderCancelled(array $data): void
    {
        $phone = $this->extractPhone($data);
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);

        TriggerAutomation::dispatch(
            $this->user->id,
            'shopify_order',
            [
                'contact_id' => $contact->id,
                'order_id' => $data['id'] ?? null,
                'order_number' => $data['order_number'] ?? $data['name'] ?? null,
                'content' => 'shopify_order_cancelled',
            ]
        )->onQueue('automations');
    }

    protected function handleCheckout(array $data): void
    {
        if (!empty($data['completed_at'])) return;

        $phone = $this->extractPhone($data);
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);

        $items = collect($data['line_items'] ?? [])->map(fn($item) => $item['title'] ?? '')->implode(', ');

        $contact->update([
            'custom_attributes' => array_merge($contact->custom_attributes ?? [], [
                'abandoned_cart_url' => $data['abandoned_checkout_url'] ?? null,
                'abandoned_cart_amount' => $data['total_price'] ?? null,
                'abandoned_cart_items' => $items,
                'abandoned_cart_date' => now()->toDateString(),
            ]),
        ]);

        $cartTag = Tag::firstOrCreate(
            ['user_id' => $this->user->id, 'name' => 'abandoned_cart'],
            ['color' => '#EF4444']
        );
        $contact->tags()->syncWithoutDetaching([$cartTag->id]);

        TriggerAutomation::dispatch(
            $this->user->id,
            'shopify_abandoned_cart',
            [
                'contact_id' => $contact->id,
                'checkout_id' => $data['id'] ?? null,
                'total_price' => $data['total_price'] ?? 0,
                'abandoned_url' => $data['abandoned_checkout_url'] ?? null,
                'items' => $items,
                'content' => 'shopify_abandoned_cart',
            ]
        )->onQueue('automations');
    }

    protected function handleCustomerCreated(array $data): void
    {
        $phone = $data['phone'] ?? ($data['default_address']['phone'] ?? null);
        if (!$phone) return;

        $this->findOrCreateContact($phone, [
            'customer' => $data,
            'email' => $data['email'] ?? null,
        ]);
    }

    protected function handleCustomerUpdated(array $data): void
    {
        $this->handleCustomerCreated($data);
    }

    public function syncOrders(int $limit = 50): array
    {
        $response = $this->apiRequest('GET', 'orders.json', [
            'limit' => $limit,
            'status' => 'any',
            'order' => 'created_at desc',
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => 'Failed to fetch orders'];
        }

        $orders = $response->json('orders', []);
        $synced = 0;

        foreach ($orders as $order) {
            $phone = $this->extractPhone($order);
            if ($phone) {
                $this->findOrCreateContact($phone, $order);
                $synced++;
            }
        }

        $this->updateStatus('active');
        $this->logEvent('orders_synced', ['count' => $synced]);

        return ['success' => true, 'synced' => $synced];
    }

    public function syncCustomers(int $limit = 50): array
    {
        $response = $this->apiRequest('GET', 'customers.json', [
            'limit' => $limit,
            'order' => 'created_at desc',
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => 'Failed to fetch customers'];
        }

        $customers = $response->json('customers', []);
        $synced = 0;

        foreach ($customers as $customer) {
            $phone = $customer['phone'] ?? ($customer['default_address']['phone'] ?? null);
            if ($phone) {
                $this->findOrCreateContact($phone, ['customer' => $customer, 'email' => $customer['email'] ?? null]);
                $synced++;
            }
        }

        $this->updateStatus('active');
        $this->logEvent('customers_synced', ['count' => $synced]);

        return ['success' => true, 'synced' => $synced];
    }

    protected function apiRequest(string $method, string $endpoint, array $data = []): \Illuminate\Http\Client\Response
    {
        $url = "https://{$this->getConfig('shop_domain')}/admin/api/{$this->apiVersion}/{$endpoint}";

        $request = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->getConfig('access_token'),
            'Content-Type' => 'application/json',
        ])->timeout(30);

        return match (strtoupper($method)) {
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url),
            default => $request->get($url, $data),
        };
    }

    protected function extractPhone(array $data): ?string
    {
        $phone = $data['phone']
            ?? $data['billing_address']['phone']
            ?? $data['shipping_address']['phone']
            ?? $data['customer']['phone']
            ?? $data['customer']['default_address']['phone']
            ?? null;

        if (!$phone) return null;

        return preg_replace('/[^0-9]/', '', $phone);
    }

    protected function findOrCreateContact(string $phone, array $data): Contact
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        $name = null;
        if (isset($data['customer'])) {
            $name = trim(($data['customer']['first_name'] ?? '') . ' ' . ($data['customer']['last_name'] ?? ''));
        } elseif (isset($data['billing_address'])) {
            $name = $data['billing_address']['name'] ?? null;
        }

        $email = $data['email'] ?? $data['contact_email'] ?? $data['customer']['email'] ?? null;

        return Contact::firstOrCreate(
            ['user_id' => $this->user->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'name' => $name ?: null,
                'email' => $email,
                'source' => 'shopify',
                'status' => 'active',
                'opted_in_at' => now(),
            ]
        );
    }
}