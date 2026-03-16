<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;

// Public Routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated Routes
Route::middleware(['auth', 'onboarding'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Business Profile
    Route::get('/settings/business', [\App\Http\Controllers\BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/settings/business', [\App\Http\Controllers\BusinessController::class, 'update'])->name('business.update');

    // Account Settings
    Route::get('/settings/account', [\App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/settings/account', [\App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
    Route::put('/settings/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('account.password');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');

    // --- Business Owner Routes ---
    Route::middleware(['role:business_owner,super_admin'])->group(function () {

        // Team Management
        Route::resource('team', \App\Http\Controllers\TeamController::class);

        // Billing & Plans
        Route::get('/billing/plans', [\App\Http\Controllers\BillingController::class, 'plans'])->name('billing.plans');
        Route::post('/billing/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
        Route::get('/billing/invoices', [\App\Http\Controllers\BillingController::class, 'invoices'])->name('billing.invoices');

        // Wallet
        Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/recharge', [\App\Http\Controllers\WalletController::class, 'recharge'])->name('wallet.recharge');
        Route::post('/wallet/recharge', [\App\Http\Controllers\WalletController::class, 'processRecharge'])->name('wallet.processRecharge');
        Route::get('/wallet/transactions', [\App\Http\Controllers\WalletController::class, 'transactions'])->name('wallet.transactions');
    });

    // Routes requiring active subscription
    Route::middleware(['subscription'])->group(function () {
 // WhatsApp Accounts
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {

        // Accounts
        Route::get('/accounts', [\App\Http\Controllers\WhatsappAccountController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/create', [\App\Http\Controllers\WhatsappAccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [\App\Http\Controllers\WhatsappAccountController::class, 'store'])->name('accounts.store');
        Route::get('/accounts/{account}', [\App\Http\Controllers\WhatsappAccountController::class, 'show'])->name('accounts.show');
        Route::post('/accounts/{account}/sync-templates', [\App\Http\Controllers\WhatsappAccountController::class, 'syncTemplates'])->name('accounts.syncTemplates');
        Route::post('/accounts/{account}/test-message', [\App\Http\Controllers\WhatsappAccountController::class, 'testMessage'])->name('accounts.testMessage');
        Route::post('/accounts/{account}/disconnect', [\App\Http\Controllers\WhatsappAccountController::class, 'disconnect'])->name('accounts.disconnect');
        Route::delete('/accounts/{account}', [\App\Http\Controllers\WhatsappAccountController::class, 'destroy'])->name('accounts.destroy');

        // Templates
        Route::get('/templates', [\App\Http\Controllers\MessageTemplateController::class, 'index'])->name('templates.index');
        Route::get('/templates/create', [\App\Http\Controllers\MessageTemplateController::class, 'create'])->name('templates.create');
        Route::post('/templates', [\App\Http\Controllers\MessageTemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}', [\App\Http\Controllers\MessageTemplateController::class, 'show'])->name('templates.show');
        Route::delete('/templates/{template}', [\App\Http\Controllers\MessageTemplateController::class, 'destroy'])->name('templates.destroy');
    });

    // Quick Message Sending
    Route::prefix('send')->name('send.')->group(function () {
        Route::post('/text', [\App\Http\Controllers\QuickMessageController::class, 'sendText'])->name('text');
        Route::post('/template', [\App\Http\Controllers\QuickMessageController::class, 'sendTemplate'])->name('template');
        Route::post('/media', [\App\Http\Controllers\QuickMessageController::class, 'sendMedia'])->name('media');
    });

    // Contacts CRM - Phase 3
    // Campaigns - Phase 3
    // Automations - Phase 4
    // Shared Inbox - Phase 4
    // Integrations - Phase 5


    });

    // --- Super Admin Routes ---
    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // More admin routes in Phase 6
    });
});

// Onboarding Routes (auth but not onboarding check)
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [OnboardingController::class, 'index'])->name('index');
    Route::post('/business-profile', [OnboardingController::class, 'saveBusinessProfile'])->name('business-profile');
    Route::post('/industry', [OnboardingController::class, 'saveIndustry'])->name('industry');
    Route::post('/plan', [OnboardingController::class, 'savePlan'])->name('plan');
    Route::post('/skip-whatsapp', [OnboardingController::class, 'skipWhatsApp'])->name('skip-whatsapp');
    Route::post('/complete', [OnboardingController::class, 'completeOnboarding'])->name('complete');
});

// WhatsApp Webhook (public, no auth)
Route::prefix('webhook')->group(function () {
    Route::get('/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'verify']);
    Route::post('/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'handle']);
});

// Payment Callbacks
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/razorpay/callback', [\App\Http\Controllers\Payment\RazorpayController::class, 'callback'])->name('razorpay.callback');
    Route::post('/razorpay/webhook', [\App\Http\Controllers\Payment\RazorpayController::class, 'webhook'])->name('razorpay.webhook');
});