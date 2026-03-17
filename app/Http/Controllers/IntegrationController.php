<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use App\Services\Integrations\IntegrationFactory;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index()
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $integrations = Integration::where('user_id', $owner->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $available = $this->getAvailableIntegrations($owner);

        return view('integrations.index', compact('integrations', 'available'));
    }

    public function create(string $type)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        $this->checkPlanAccess($owner, $type);

        $existing = Integration::where('user_id', $owner->id)->where('type', $type)->first();

        return view("integrations.setup.{$type}", [
            'type' => $type,
            'existing' => $existing,
        ]);
    }

    public function store(Request $request, string $type)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        $this->checkPlanAccess($owner, $type);

        $rules = $this->getValidationRules($type);
        $validated = $request->validate($rules);

        $integration = Integration::updateOrCreate(
            ['user_id' => $owner->id, 'type' => $type],
            ['name' => $this->getIntegrationName($type), 'status' => 'inactive', 'config' => []]
        );

        $service = IntegrationFactory::make($type);
        $service->setIntegration($integration);

        if ($service->connect($validated)) {
            \App\Services\ActivityLogger::log('integration_connected', 'Integration', $integration->id);
            return redirect()->route('integrations.show', $integration)
                ->with('success', ucfirst($type) . ' connected successfully!');
        }

        return back()->with('error', 'Failed to connect. Please check your credentials.')
            ->withInput();
    }

    public function show(Integration $integration)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($integration->user_id !== $owner->id, 403);

        $logs = $integration->logs()->orderBy('created_at', 'desc')->limit(20)->get();

        return view('integrations.show', compact('integration', 'logs'));
    }

    public function sync(Integration $integration, string $action)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($integration->user_id !== $owner->id, 403);

        $service = IntegrationFactory::fromIntegration($integration);

        $result = match ($action) {
            'orders' => method_exists($service, 'syncOrders') ? $service->syncOrders() : ['success' => false, 'error' => 'Not supported'],
            'customers' => method_exists($service, 'syncCustomers') ? $service->syncCustomers() : ['success' => false, 'error' => 'Not supported'],
            'import_contacts' => method_exists($service, 'importContacts') ? $service->importContacts() : ['success' => false, 'error' => 'Not supported'],
            'export_contacts' => method_exists($service, 'exportContacts') ? $service->exportContacts() : ['success' => false, 'error' => 'Not supported'],
            'test' => ['success' => $service->testConnection()],
            default => ['success' => false, 'error' => 'Unknown action'],
        };

        if ($result['success']) {
            $msg = match ($action) {
                'orders' => "Synced {$result['synced']} orders.",
                'customers' => "Synced {$result['synced']} customers.",
                'import_contacts' => "Imported {$result['imported']} contacts. {$result['skipped']} skipped.",
                'export_contacts' => "Exported {$result['exported']} contacts.",
                'test' => 'Connection successful!',
                default => 'Action completed.',
            };
            return back()->with('success', $msg);
        }

        return back()->with('error', $result['error'] ?? 'Action failed.');
    }

    public function disconnect(Integration $integration)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($integration->user_id !== $owner->id, 403);

        $service = IntegrationFactory::fromIntegration($integration);
        $service->disconnect();

        return back()->with('success', 'Integration disconnected.');
    }

    public function destroy(Integration $integration)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($integration->user_id !== $owner->id, 403);

        $service = IntegrationFactory::fromIntegration($integration);
        $service->disconnect();
        $integration->delete();

        return redirect()->route('integrations.index')->with('success', 'Integration removed.');
    }

    protected function getAvailableIntegrations($user): array
    {
        $plan = $user->getActiveSubscription()?->plan;

        return [
            [
                'type' => 'shopify',
                'name' => 'Shopify',
                'icon' => 'fab fa-shopify',
                'color' => '#96BF48',
                'description' => 'Sync orders, customers, abandoned carts and automate order updates.',
                'features' => ['Order confirmation', 'Shipping updates', 'Abandoned cart recovery', 'COD verification'],
                'available' => $plan?->shopify_integration ?? false,
            ],
            [
                'type' => 'woocommerce',
                'name' => 'WooCommerce',
                'icon' => 'fab fa-wordpress',
                'color' => '#96588A',
                'description' => 'Connect your WooCommerce store for order notifications and customer sync.',
                'features' => ['Order notifications', 'Customer sync', 'Status updates'],
                'available' => $plan?->woocommerce_integration ?? false,
            ],
            [
                'type' => 'google_sheets',
                'name' => 'Google Sheets',
                'icon' => 'fas fa-table',
                'color' => '#0F9D58',
                'description' => 'Import/export contacts, store leads and sync campaign data.',
                'features' => ['Import contacts', 'Export contacts', 'Store leads', 'Campaign reporting'],
                'available' => $plan?->google_sheets_integration ?? false,
            ],
        ];
    }

    protected function getValidationRules(string $type): array
    {
        return match ($type) {
            'shopify' => [
                'shop_domain' => 'required|string|max:255',
                'access_token' => 'required|string',
                'sync_orders' => 'nullable|boolean',
                'sync_customers' => 'nullable|boolean',
                'abandoned_cart' => 'nullable|boolean',
                'order_confirmation' => 'nullable|boolean',
                'shipping_updates' => 'nullable|boolean',
                'cod_verification' => 'nullable|boolean',
            ],
            'woocommerce' => [
                'store_url' => 'required|url',
                'consumer_key' => 'required|string',
                'consumer_secret' => 'required|string',
                'sync_orders' => 'nullable|boolean',
                'order_confirmation' => 'nullable|boolean',
                'shipping_updates' => 'nullable|boolean',
            ],
            'google_sheets' => [
                'spreadsheet_url' => 'required|string',
                'api_key' => 'nullable|string',
                'service_account_json' => 'nullable|string',
                'default_sheet' => 'nullable|string',
                'sync_direction' => 'nullable|in:import,export,both',
                'header_row' => 'nullable|boolean',
                'auto_sync' => 'nullable|boolean',
            ],
            default => [],
        };
    }

    protected function getIntegrationName(string $type): string
    {
        return match ($type) {
            'shopify' => 'Shopify',
            'woocommerce' => 'WooCommerce',
            'google_sheets' => 'Google Sheets',
            default => ucfirst($type),
        };
    }

    protected function checkPlanAccess($user, string $type): void
    {
        $plan = $user->getActiveSubscription()?->plan;

        $hasAccess = match ($type) {
            'shopify' => $plan?->shopify_integration ?? false,
            'woocommerce' => $plan?->woocommerce_integration ?? false,
            'google_sheets' => $plan?->google_sheets_integration ?? false,
            default => false,
        };

        if (!$hasAccess) {
            abort(403, 'This integration is not available on your plan. Please upgrade.');
        }
    }
}