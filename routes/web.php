<?php

use Illuminate\Support\Facades\Route;

// =============================================
// MARKETING WEBSITE (PUBLIC - NO AUTH)
// =============================================
Route::name('website.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Website\HomeController::class, 'index'])->name('home');
    Route::get('/features', [\App\Http\Controllers\Website\HomeController::class, 'features'])->name('features');
    Route::get('/pricing', [\App\Http\Controllers\Website\HomeController::class, 'pricing'])->name('pricing');
    Route::get('/about', [\App\Http\Controllers\Website\HomeController::class, 'about'])->name('about');
    Route::get('/contact', [\App\Http\Controllers\Website\HomeController::class, 'contact'])->name('contact');
    Route::post('/contact', [\App\Http\Controllers\Website\HomeController::class, 'submitContact'])->name('contact.submit');
    Route::get('/privacy-policy', [\App\Http\Controllers\Website\HomeController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [\App\Http\Controllers\Website\HomeController::class, 'terms'])->name('terms');
    Route::get('/refund-policy', [\App\Http\Controllers\Website\HomeController::class, 'refund'])->name('refund');

    // Partner Public Page
    Route::get('/partner-program', [\App\Http\Controllers\Website\PartnerPageController::class, 'index'])->name('partner');
    Route::post('/partner-program/apply', [\App\Http\Controllers\Website\PartnerPageController::class, 'apply'])->name('partner.apply');

    // Use Cases
    Route::get('/use-cases', [\App\Http\Controllers\Website\UseCaseController::class, 'index'])->name('usecases');
    Route::get('/use-cases/{slug}', [\App\Http\Controllers\Website\UseCaseController::class, 'show'])->name('usecases.show');

    // Industries
    Route::get('/industries', [\App\Http\Controllers\Website\UseCaseController::class, 'industries'])->name('industries');
    Route::get('/industries/{slug}', [\App\Http\Controllers\Website\UseCaseController::class, 'industryShow'])->name('industries.show');

    // Blog
    Route::get('/blog', [\App\Http\Controllers\Website\BlogController::class, 'index'])->name('blog');
    Route::get('/blog/category/{category}', [\App\Http\Controllers\Website\BlogController::class, 'category'])->name('blog.category');
    Route::get('/blog/{slug}', [\App\Http\Controllers\Website\BlogController::class, 'show'])->name('blog.show');

    // Sitemap
    Route::get('/sitemap.xml', [\App\Http\Controllers\Website\SitemapController::class, 'index'])->name('sitemap');
});

// =============================================
// GUEST AUTH ROUTES
// =============================================
Route::middleware('guest')->group(function () {
    Route::get('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisterController::class, 'register']);
    Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login']);
});

Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout')->middleware('auth');

// =============================================
// ONBOARDING
// =============================================
Route::middleware('auth')->prefix('onboarding')->name('onboarding.')->group(function () {
    Route::get('/', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('index');
    Route::post('/business-profile', [\App\Http\Controllers\OnboardingController::class, 'saveBusinessProfile'])->name('business-profile');
    Route::post('/industry', [\App\Http\Controllers\OnboardingController::class, 'saveIndustry'])->name('industry');
    Route::post('/plan', [\App\Http\Controllers\OnboardingController::class, 'savePlan'])->name('plan');
    Route::post('/skip-whatsapp', [\App\Http\Controllers\OnboardingController::class, 'skipWhatsApp'])->name('skip-whatsapp');
    Route::post('/complete', [\App\Http\Controllers\OnboardingController::class, 'completeOnboarding'])->name('complete');
});

// =============================================
// SUPER ADMIN ROUTES
// =============================================
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [\App\Http\Controllers\Admin\AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::post('/users/{user}/add-credits', [\App\Http\Controllers\Admin\AdminUserController::class, 'addCredits'])->name('users.addCredits');
    Route::post('/users/{user}/login-as', [\App\Http\Controllers\Admin\AdminUserController::class, 'loginAs'])->name('users.loginAs');
    Route::get('/switch-back', [\App\Http\Controllers\Admin\AdminUserController::class, 'switchBack'])->name('switchBack');

    Route::get('/partners', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'index'])->name('partners.index');
    Route::post('/partners/{partner}/approve', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'approve'])->name('partners.approve');
    Route::post('/partners/{partner}/reject', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'reject'])->name('partners.reject');
    Route::put('/partners/{partner}/commission', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'updateCommission'])->name('partners.updateCommission');

    Route::get('/payouts', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'payouts'])->name('payouts.index');
    Route::post('/payouts/{payout}/process', [\App\Http\Controllers\Admin\AdminPartnerController::class, 'processPayout'])->name('payouts.process');

    Route::resource('plans', \App\Http\Controllers\Admin\AdminPlanController::class);

    Route::get('/settings', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/general', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateGeneral'])->name('settings.general');
    Route::post('/settings/whatsapp', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateWhatsapp'])->name('settings.whatsapp');
    Route::post('/settings/payment', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updatePayment'])->name('settings.payment');
    Route::post('/settings/mail', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateMail'])->name('settings.mail');
    Route::post('/settings/mail/test', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'testMail'])->name('settings.testMail');
    Route::post('/settings/messaging', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateMessaging'])->name('settings.messaging');

    Route::resource('blog', \App\Http\Controllers\Admin\AdminBlogController::class);
});

// =============================================
// PARTNER PANEL ROUTES (Authenticated)
// =============================================
Route::middleware(['auth', 'onboarding'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/apply', [\App\Http\Controllers\PartnerController::class, 'apply'])->name('apply');
    Route::post('/apply', [\App\Http\Controllers\PartnerController::class, 'submitApplication'])->name('submitApplication');
    Route::get('/dashboard', [\App\Http\Controllers\PartnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/payouts', [\App\Http\Controllers\PartnerController::class, 'payouts'])->name('payouts');
    Route::post('/payouts/request', [\App\Http\Controllers\PartnerController::class, 'requestPayout'])->name('requestPayout');
    Route::get('/settings', [\App\Http\Controllers\PartnerController::class, 'settings'])->name('settings');
    Route::put('/settings', [\App\Http\Controllers\PartnerController::class, 'updateSettings'])->name('updateSettings');
});

// =============================================
// PAYMENT
// =============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/payment/razorpay/create-order', [\App\Http\Controllers\Payment\RazorpayController::class, 'createOrder'])->name('payment.razorpay.createOrder');
    Route::post('/payment/razorpay/callback', [\App\Http\Controllers\Payment\RazorpayController::class, 'callback'])->name('payment.razorpay.callback');
});

// =============================================
// AUTHENTICATED ROUTES
// =============================================
Route::middleware(['auth', 'onboarding'])->group(function () {

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings/business', [\App\Http\Controllers\BusinessController::class, 'edit'])->name('business.edit');
    Route::put('/settings/business', [\App\Http\Controllers\BusinessController::class, 'update'])->name('business.update');
    Route::get('/settings/account', [\App\Http\Controllers\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/settings/account', [\App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
    Route::put('/settings/password', [\App\Http\Controllers\AccountController::class, 'updatePassword'])->name('account.password');

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');

    Route::middleware(['role:business_owner,super_admin'])->group(function () {
        Route::resource('team', \App\Http\Controllers\TeamController::class);
        Route::get('/billing/plans', [\App\Http\Controllers\BillingController::class, 'plans'])->name('billing.plans');
        Route::post('/billing/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
        Route::get('/billing/invoices', [\App\Http\Controllers\BillingController::class, 'invoices'])->name('billing.invoices');
        Route::get('/wallet', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/recharge', [\App\Http\Controllers\WalletController::class, 'recharge'])->name('wallet.recharge');
        Route::post('/wallet/recharge', [\App\Http\Controllers\WalletController::class, 'processRecharge'])->name('wallet.processRecharge');
        Route::get('/wallet/transactions', [\App\Http\Controllers\WalletController::class, 'transactions'])->name('wallet.transactions');
    });

    Route::middleware(['subscription'])->group(function () {

        // WhatsApp
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/accounts', [\App\Http\Controllers\WhatsappAccountController::class, 'index'])->name('accounts.index');
            Route::get('/accounts/embedded-signup', [\App\Http\Controllers\WhatsappAccountController::class, 'embeddedSignup'])->name('accounts.embeddedSignup');
            Route::post('/accounts/embedded-signup/callback', [\App\Http\Controllers\WhatsappAccountController::class, 'embeddedSignupCallback'])->name('accounts.embeddedSignupCallback');
            Route::get('/accounts/create', [\App\Http\Controllers\WhatsappAccountController::class, 'create'])->name('accounts.create');
            Route::post('/accounts', [\App\Http\Controllers\WhatsappAccountController::class, 'store'])->name('accounts.store');
            Route::get('/accounts/{account}', [\App\Http\Controllers\WhatsappAccountController::class, 'show'])->name('accounts.show');
            Route::post('/accounts/{account}/sync-templates', [\App\Http\Controllers\WhatsappAccountController::class, 'syncTemplates'])->name('accounts.syncTemplates');
            Route::post('/accounts/{account}/test-message', [\App\Http\Controllers\WhatsappAccountController::class, 'testMessage'])->name('accounts.testMessage');
            Route::post('/accounts/{account}/disconnect', [\App\Http\Controllers\WhatsappAccountController::class, 'disconnect'])->name('accounts.disconnect');
            Route::delete('/accounts/{account}', [\App\Http\Controllers\WhatsappAccountController::class, 'destroy'])->name('accounts.destroy');

            Route::get('/templates', [\App\Http\Controllers\MessageTemplateController::class, 'index'])->name('templates.index');
            Route::get('/templates/create', [\App\Http\Controllers\MessageTemplateController::class, 'create'])->name('templates.create');
            Route::post('/templates', [\App\Http\Controllers\MessageTemplateController::class, 'store'])->name('templates.store');
            Route::get('/templates/{template}', [\App\Http\Controllers\MessageTemplateController::class, 'show'])->name('templates.show');
            Route::delete('/templates/{template}', [\App\Http\Controllers\MessageTemplateController::class, 'destroy'])->name('templates.destroy');
        });

        // Inbox
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

        // Contacts
        Route::resource('contacts', \App\Http\Controllers\ContactController::class);
        Route::post('/contacts/bulk-action', [\App\Http\Controllers\ContactController::class, 'bulkAction'])->name('contacts.bulkAction');
        Route::get('/contacts-import', [\App\Http\Controllers\ContactController::class, 'importForm'])->name('contacts.import.form');
        Route::post('/contacts-import', [\App\Http\Controllers\ContactController::class, 'import'])->name('contacts.import');
        Route::get('/contacts-export', [\App\Http\Controllers\ContactController::class, 'export'])->name('contacts.export');

        // Tags
        Route::resource('tags', \App\Http\Controllers\TagController::class)->except(['create', 'show', 'edit']);

        // Campaigns
        Route::resource('campaigns', \App\Http\Controllers\CampaignController::class)->except(['edit', 'update']);
        Route::post('/campaigns/{campaign}/pause', [\App\Http\Controllers\CampaignController::class, 'pause'])->name('campaigns.pause');
        Route::post('/campaigns/{campaign}/resume', [\App\Http\Controllers\CampaignController::class, 'resume'])->name('campaigns.resume');
        Route::post('/campaigns/{campaign}/cancel', [\App\Http\Controllers\CampaignController::class, 'cancel'])->name('campaigns.cancel');
        Route::post('/campaigns/{campaign}/duplicate', [\App\Http\Controllers\CampaignController::class, 'duplicate'])->name('campaigns.duplicate');

        // Automations
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

        // Integrations
        Route::prefix('integrations')->name('integrations.')->group(function () {
            Route::get('/', [\App\Http\Controllers\IntegrationController::class, 'index'])->name('index');
            Route::get('/setup/{type}', [\App\Http\Controllers\IntegrationController::class, 'create'])->name('create');
            Route::post('/setup/{type}', [\App\Http\Controllers\IntegrationController::class, 'store'])->name('store');
            Route::get('/{integration}', [\App\Http\Controllers\IntegrationController::class, 'show'])->name('show');
            Route::post('/{integration}/sync/{action}', [\App\Http\Controllers\IntegrationController::class, 'sync'])->name('sync');
            Route::post('/{integration}/disconnect', [\App\Http\Controllers\IntegrationController::class, 'disconnect'])->name('disconnect');
            Route::delete('/{integration}', [\App\Http\Controllers\IntegrationController::class, 'destroy'])->name('destroy');
        });

        // Quick Send
        Route::prefix('send')->name('send.')->group(function () {
            Route::post('/text', [\App\Http\Controllers\QuickMessageController::class, 'sendText'])->name('text');
            Route::post('/template', [\App\Http\Controllers\QuickMessageController::class, 'sendTemplate'])->name('template');
            Route::post('/media', [\App\Http\Controllers\QuickMessageController::class, 'sendMedia'])->name('media');
        });
    });
});