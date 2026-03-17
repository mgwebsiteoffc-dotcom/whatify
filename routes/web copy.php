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

        // === CONTACTS CRM ===
    Route::resource('contacts', \App\Http\Controllers\ContactController::class);
    Route::post('/contacts/bulk-action', [\App\Http\Controllers\ContactController::class, 'bulkAction'])->name('contacts.bulkAction');
    Route::get('/contacts-import', [\App\Http\Controllers\ContactController::class, 'importForm'])->name('contacts.import.form');
    Route::post('/contacts-import', [\App\Http\Controllers\ContactController::class, 'import'])->name('contacts.import');
    Route::get('/contacts-export', [\App\Http\Controllers\ContactController::class, 'export'])->name('contacts.export');

    // === TAGS ===
    Route::resource('tags', \App\Http\Controllers\TagController::class)->except(['create', 'show', 'edit']);

    // === CAMPAIGNS ===
    Route::resource('campaigns', \App\Http\Controllers\CampaignController::class)->except(['edit', 'update']);
    Route::post('/campaigns/{campaign}/pause', [\App\Http\Controllers\CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume', [\App\Http\Controllers\CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('/campaigns/{campaign}/cancel', [\App\Http\Controllers\CampaignController::class, 'cancel'])->name('campaigns.cancel');
    Route::post('/campaigns/{campaign}/duplicate', [\App\Http\Controllers\CampaignController::class, 'duplicate'])->name('campaigns.duplicate');

        Route::prefix('inbox')->name('inbox.')->group(function () {
        Route::get('/', [\App\Http\Controllers\InboxController::class, 'index'])->name('index');
        Route::post('/{conversation}/reply', [\App\Http\Controllers\InboxController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/send-template', [\App\Http\Controllers\InboxController::class, 'sendTemplateMessage'])->name('sendTemplate');
        Route::post('/{conversation}/send-media', [\App\Http\Controllers\InboxController::class, 'sendMediaMessage'])->name('sendMedia');
        Route::post('/{conversation}/assign', [\App\Http\Controllers\InboxController::class, 'assign'])->name('assign');
        Route::post('/{conversation}/status', [\App\Http\Controllers\InboxController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{conversation}/toggle-bot', [\App\Http\Controllers\InboxController::class, 'toggleBot'])->name('toggleBot');
        Route::post('/{conversation}/note', [\App\Http\Controllers\InboxController::class, 'addNote'])->name('addNote');
    });

    // === AUTOMATIONS ===
    Route::prefix('automations')->name('automations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AutomationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AutomationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\AutomationController::class, 'store'])->name('store');
        Route::get('/{automation}', [\App\Http\Controllers\AutomationController::class, 'show'])->name('show');
        Route::get('/{automation}/builder', [\App\Http\Controllers\AutomationController::class, 'builder'])->name('builder');
        Route::post('/{automation}/save-flow', [\App\Http\Controllers\AutomationController::class, 'saveFlow'])->name('saveFlow');
        Route::post('/{automation}/toggle', [\App\Http\Controllers\AutomationController::class, 'toggleStatus'])->name('toggle');
        Route::post('/{automation}/duplicate', [\App\Http\Controllers\AutomationController::class, 'duplicate'])->name('duplicate');
        Route::delete('/{automation}', [\App\Http\Controllers\AutomationController::class, 'destroy'])->name('destroy');
    });

    // Contacts CRM - Phase 3
    // Campaigns - Phase 3
    // Automations - Phase 4
    // Shared Inbox - Phase 4
    // Integrations - Phase 5

        // === INTEGRATIONS ===
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('index');
        Route::get('/setup/{type}', [\App\Http\Controllers\IntegrationController::class, 'create'])->name('create');
        Route::post('/setup/{type}', [\App\Http\Controllers\IntegrationController::class, 'store'])->name('store');
        Route::get('/{integration}', [\App\Http\Controllers\IntegrationController::class, 'show'])->name('show');
        Route::post('/{integration}/sync/{action}', [\App\Http\Controllers\IntegrationController::class, 'sync'])->name('sync');
        Route::post('/{integration}/disconnect', [\App\Http\Controllers\IntegrationController::class, 'disconnect'])->name('disconnect');
        Route::delete('/{integration}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->name('destroy');
    });


    });

    // --- Super Admin Routes ---
    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // More admin routes in Phase 6
    });
});

Route::middleware(['auth'])->group(function () {
    Route::post('/payment/razorpay/create-order', [\App\Http\Controllers\Payment\RazorpayController::class, 'createOrder'])->name('payment.razorpay.createOrder');
    Route::post('/payment/razorpay/callback', [\App\Http\Controllers\Payment\RazorpayController::class, 'callback'])->name('payment.razorpay.callback');
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
Route::prefix('webhook')->middleware('throttle:webhook')->group(function () {
    Route::get('/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'verify']);
    Route::post('/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'handle']);
});
// Payment Callbacks
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/razorpay/callback', [\App\Http\Controllers\Payment\RazorpayController::class, 'callback'])->name('razorpay.callback');
    Route::post('/razorpay/webhook', [\App\Http\Controllers\Payment\RazorpayController::class, 'webhook'])->name('razorpay.webhook');
});

// === PARTNER ROUTES ===
Route::middleware(['auth', 'onboarding'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/apply', [\App\Http\Controllers\PartnerController::class, 'apply'])->name('apply');
    Route::post('/apply', [\App\Http\Controllers\PartnerController::class, 'submitApplication'])->name('submitApplication');

    Route::middleware(['role:partner,business_owner,super_admin'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\PartnerController::class, 'dashboard'])->name('dashboard');
        Route::get('/payouts', [\App\Http\Controllers\PartnerController::class, 'payouts'])->name('payouts');
        Route::post('/payouts/request', [\App\Http\Controllers\PartnerController::class, 'requestPayout'])->name('requestPayout');
        Route::get('/settings', [\App\Http\Controllers\PartnerController::class, 'settings'])->name('settings');
        Route::put('/settings', [\App\Http\Controllers\PartnerController::class, 'updateSettings'])->name('updateSettings');
    });
});

// === ANALYTICS ===
Route::middleware(['auth', 'onboarding', 'subscription'])->group(function () {
    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');
});

// === SUPER ADMIN ROUTES ===
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::post('/users/{user}/add-credits', [\App\Http\Controllers\Admin\AdminUserController::class, 'addCredits'])->name('users.addCredits');
    Route::post('/users/{user}/login-as', [\App\Http\Controllers\Admin\AdminUserController::class, 'loginAs'])->name('users.loginAs');
    Route::get('/switch-back', [\App\Http\Controllers\Admin\AdminUserController::class, 'switchBack'])->name('switchBack');

    // Partners
    Route::get('/partners', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'index'])->name('partners.index');
    Route::post('/partners/{partner}/approve', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'approve'])->name('partners.approve');
    Route::post('/partners/{partner}/reject', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'reject'])->name('partners.reject');
    Route::put('/partners/{partner}/commission', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'updateCommission'])->name('partners.updateCommission');
    Route::get('/payouts', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'payouts'])->name('payouts.index');
    Route::post('/payouts/{payout}/process', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'processPayout'])->name('payouts.process');

    // Plans
    Route::resource('plans', \App\Http\Controllers\Admin\AdminPlanController::class);
});