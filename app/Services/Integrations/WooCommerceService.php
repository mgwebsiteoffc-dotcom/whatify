<?php

namespace App\Services\Integrations;

use App\Models\Contact;
use App\Models\Tag;
use App\Jobs\TriggerAutomation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService extends BaseIntegrationService
{
    public function getType(): string
    {
        return 'woocommerce';
    }

    public function connect(array $config): bool
    {
        try {
            $response = Http::withBasicAuth($config['consumer_key'], $config['consumer_secret'])
                ->get(rtrim($config['store_url'], '/') . '/wp-json/wc/v3/system_status');

            if ($response->successful()) {
                $storeInfo = $response->json();

                $this->integration->update([
                    'config' => [
                        'store_url' => rtrim($config['store_url'], '/'),
                        'consumer_key' => $config['consumer_key'],
                        'consumer_secret' => $config['consumer_secret'],
                        'store_name' => $storeInfo['environment']['site_title'] ?? $config['store_url'],
                        'wc_version' => $storeInfo['environment']['version'] ?? 'unknown',
                        'webhooks_registered' => false,
                        'sync_orders' => $config['sync_orders'] ?? true,
                        'order_confirmation' => $config['order_confirmation'] ?? true,
                        'shipping_updates' => $config['shipping_updates'] ?? true,
                    ],
                    'status' => 'active',
                    'last_synced_at' => now(),
                ]);

                $this->registerWebhooks();
                $this->logEvent('connected', ['store' => $config['store_url']]);
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
            $response = Http::withBasicAuth(
                $this->getConfig('consumer_key'),
                $this->getConfig('consumer_secret')
            )->get($this->getConfig('store_url') . '/wp-json/wc/v3/system_status');

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function registerWebhooks(): void
    {
        $webhookUrl = url("/api/integrations/woocommerce/webhook/{$this->integration->id}");

        $topics = [
            'order.created' => 'Order Created',
            'order.updated' => 'Order Updated',
            'customer.created' => 'Customer Created',
        ];

        foreach ($topics as $topic => $name) {
            try {
                $this->apiRequest('POST', 'webhooks', [
                    'name' => "Whatify - {$name}",
                    'topic' => $topic,
                    'delivery_url' => $webhookUrl,
                    'secret' => $this->integration->id . '_' . md5($this->getConfig('consumer_key')),
                    'status' => 'active',
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to register WooCommerce webhook: {$topic}", ['error' => $e->getMessage()]);
            }
        }

        $this->updateConfig(['webhooks_registered' => true]);
    }

    public function removeWebhooks(): void
    {
        try {
            $response = $this->apiRequest('GET', 'webhooks', ['per_page' => 100]);

            if ($response->successful()) {
                foreach ($response->json() as $webhook) {
                    if (str_contains($webhook['name'] ?? '', 'Whatify')) {
                        $this->apiRequest('DELETE', "webhooks/{$webhook['id']}", ['force' => true]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to remove WooCommerce webhooks', ['error' => $e->getMessage()]);
        }

        $this->updateConfig(['webhooks_registered' => false]);
    }

    public function handleWebhook(string $topic, array $data): void
    {
        $this->logEvent("webhook_{$topic}", $data);

        match ($topic) {
            'order.created' => $this->handleOrderCreated($data),
            'order.updated' => $this->handleOrderUpdated($data),
            'customer.created' => $this->handleCustomerCreated($data),
            default => Log::info("Unhandled WooCommerce webhook: {$topic}"),
        };
    }

    protected function handleOrderCreated(array $data): void
    {
        $phone = $data['billing']['phone'] ?? null;
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);

        $orderTag = Tag::firstOrCreate(
            ['user_id' => $this->user->id, 'name' => 'woo_customer'],
            ['color' => '#7C3AED']
        );
        $contact->tags()->syncWithoutDetaching([$orderTag->id]);

        $items = collect($data['line_items'] ?? [])->map(fn($i) => $i['name'] ?? '')->implode(', ');

        $contact->update([
            'custom_attributes' => array_merge($contact->custom_attributes ?? [], [
                'last_order_id' => $data['id'] ?? null,
                'last_order_number' => $data['number'] ?? null,
                'last_order_amount' => $data['total'] ?? null,
                'last_order_date' => now()->toDateString(),
                'last_order_items' => $items,
                'payment_method' => $data['payment_method_title'] ?? null,
            ]),
        ]);

        TriggerAutomation::dispatch(
            $this->user->id,
            'woocommerce_order',
            [
                'contact_id' => $contact->id,
                'order_id' => $data['id'] ?? null,
                'order_number' => $data['number'] ?? null,
                'total' => $data['total'] ?? 0,
                'status' => $data['status'] ?? 'processing',
                'items' => $items,
                'content' => 'woocommerce_order_created',
            ]
        )->onQueue('automations');
    }

    protected function handleOrderUpdated(array $data): void
    {
        $phone = $data['billing']['phone'] ?? null;
        if (!$phone) return;

        $contact = $this->findOrCreateContact($phone, $data);
        $status = $data['status'] ?? '';

        if ($status === 'completed') {
            TriggerAutomation::dispatch(
                $this->user->id,
                'woocommerce_order',
                [
                    'contact_id' => $contact->id,
                    'order_id' => $data['id'] ?? null,
                    'status' => 'completed',
                    'content' => 'woocommerce_order_completed',
                ]
            )->onQueue('automations');
        }
    }

    protected function handleCustomerCreated(array $data): void
    {
        $phone = $data['billing']['phone'] ?? null;
        if (!$phone) return;

        $this->findOrCreateContact($phone, $data);
    }

    public function syncOrders(int $limit = 50): array
    {
        $response = $this->apiRequest('GET', 'orders', [
            'per_page' => $limit,
            'orderby' => 'date',
            'order' => 'desc',
        ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => 'Failed to fetch orders'];
        }

        $synced = 0;
        foreach ($response->json() as $order) {
            $phone = $order['billing']['phone'] ?? null;
            if ($phone) {
                $this->findOrCreateContact($phone, $order);
                $synced++;
            }
        }

        $this->updateStatus('active');
        $this->logEvent('orders_synced', ['count' => $synced]);

        return ['success' => true, 'synced' => $synced];
    }

    protected function apiRequest(string $method, string $endpoint, array $data = []): \Illuminate\Http\Client\Response
    {
        $url = $this->getConfig('store_url') . '/wp-json/wc/v3/' . $endpoint;

        $request = Http::withBasicAuth(
            $this->getConfig('consumer_key'),
            $this->getConfig('consumer_secret')
        )->timeout(30);

        return match (strtoupper($method)) {
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url, $data),
            default => $request->get($url, $data),
        };
    }

    protected function findOrCreateContact(string $phone, array $data): Contact
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $localPhone = substr($phone, -10);
        $countryCode = strlen($phone) > 10 ? substr($phone, 0, strlen($phone) - 10) : '91';

        $billing = $data['billing'] ?? [];
        $name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $email = $billing['email'] ?? $data['email'] ?? null;

        return Contact::firstOrCreate(
            ['user_id' => $this->user->id, 'phone' => $localPhone],
            [
                'country_code' => $countryCode,
                'name' => $name ?: null,
                'email' => $email,
                'source' => 'woocommerce',
                'status' => 'active',
                'opted_in_at' => now(),
            ]
        );
    }
}