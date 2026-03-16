<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

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
    });

    // External API (API Key auth)
    Route::middleware('auth:sanctum')->prefix('external')->group(function () {
        Route::post('/send-message', [\App\Http\Controllers\Api\ExternalApiController::class, 'sendMessage']);
        Route::post('/send-template', [\App\Http\Controllers\Api\ExternalApiController::class, 'sendTemplate']);
        Route::get('/contacts', [\App\Http\Controllers\Api\ExternalApiController::class, 'contacts']);
        Route::post('/contacts', [\App\Http\Controllers\Api\ExternalApiController::class, 'createContact']);
    });
});