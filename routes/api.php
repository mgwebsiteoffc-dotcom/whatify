<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

// WhatsApp Webhook (no auth, no CSRF)
Route::get('/webhook/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'handle']);

// Payment Callbacks (no auth, no CSRF)
Route::post('/payment/razorpay/webhook', [\App\Http\Controllers\Payment\RazorpayController::class, 'webhook'])->name('payment.razorpay.webhook');

// Mobile App API
Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/login', [ApiAuthController::class, 'login']);

    // Authenticated Mobile API Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::get('/profile', [ApiAuthController::class, 'profile']);
        Route::post('/logout', [ApiAuthController::class, 'logout']);

        // Device Token Registration (for push notifications)
        Route::post('/device-token', [\App\Http\Controllers\Api\DeviceTokenController::class, 'store']);
        Route::delete('/device-token', [\App\Http\Controllers\Api\DeviceTokenController::class, 'destroy']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationApiController::class, 'index']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationApiController::class, 'markAllRead']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationApiController::class, 'unreadCount']);

        // Dashboard
        Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardApiController::class, 'stats']);

        // Conversations (for inbox)
        Route::get('/conversations', [\App\Http\Controllers\Api\ConversationApiController::class, 'index']);
        Route::get('/conversations/{conversation}', [\App\Http\Controllers\Api\ConversationApiController::class, 'show']);
        Route::get('/conversations/{conversation}/messages', [\App\Http\Controllers\Api\ConversationApiController::class, 'messages']);
        Route::post('/conversations/{conversation}/reply', [\App\Http\Controllers\Api\ConversationApiController::class, 'reply']);
        Route::post('/conversations/{conversation}/assign', [\App\Http\Controllers\Api\ConversationApiController::class, 'assign']);
        Route::post('/conversations/{conversation}/status', [\App\Http\Controllers\Api\ConversationApiController::class, 'updateStatus']);

        // Contacts
        Route::get('/contacts', [\App\Http\Controllers\Api\ContactApiController::class, 'index']);
        Route::get('/contacts/{contact}', [\App\Http\Controllers\Api\ContactApiController::class, 'show']);

        // Campaigns
        Route::get('/campaigns', [\App\Http\Controllers\Api\CampaignApiController::class, 'index']);
        Route::get('/campaigns/{campaign}', [\App\Http\Controllers\Api\CampaignApiController::class, 'show']);

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\Api\WalletApiController::class, 'index']);

        // Conversations - additional endpoints
Route::post('/conversations/{conversation}/send-template', [\App\Http\Controllers\Api\ConversationApiController::class, 'sendTemplate']);
Route::post('/conversations/{conversation}/toggle-bot', [\App\Http\Controllers\Api\ConversationApiController::class, 'toggleBot']);
Route::post('/conversations/{conversation}/note', [\App\Http\Controllers\Api\ConversationApiController::class, 'addNote']);

// Automations
Route::get('/automations', function (Request $request) {
    $owner = $request->user()->getBusinessOwner() ?? $request->user();
    return response()->json(
        \App\Models\Automation::where('user_id', $owner->id)
            ->select('id', 'name', 'trigger_type', 'status', 'execution_count', 'created_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
    );
});

Route::post('/automations/{automation}/toggle', function (Request $request, \App\Models\Automation $automation) {
    $owner = $request->user()->getBusinessOwner() ?? $request->user();
    if ($automation->user_id !== $owner->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    $automation->update([
        'status' => $automation->status === 'active' ? 'inactive' : 'active',
    ]);
    return response()->json(['success' => true, 'status' => $automation->fresh()->status]);
});
    });

    // External API (API Key auth)
    Route::middleware('auth:sanctum')->prefix('external')->group(function () {
        Route::post('/send-message', [\App\Http\Controllers\Api\ExternalApiController::class, 'sendMessage']);
        Route::post('/send-template', [\App\Http\Controllers\Api\ExternalApiController::class, 'sendTemplate']);
        Route::get('/contacts', [\App\Http\Controllers\Api\ExternalApiController::class, 'contacts']);
        Route::post('/contacts', [\App\Http\Controllers\Api\ExternalApiController::class, 'createContact']);
    });
});

Route::prefix('integrations')->group(function () {
    Route::post('/shopify/webhook/{integrationId}', [\App\Http\Controllers\Webhook\IntegrationWebhookController::class, 'shopify']);
    Route::post('/woocommerce/webhook/{integrationId}', [\App\Http\Controllers\Webhook\IntegrationWebhookController::class, 'woocommerce']);
});