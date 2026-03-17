<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'general' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'currency' => config('whatify.wallet.currency', 'INR'),
            ],
            'whatsapp' => [
                'api_url' => config('whatify.whatsapp.api_url'),
                'api_version' => config('whatify.whatsapp.api_version'),
                'app_id' => config('whatify.whatsapp.app_id'),
                'app_secret' => config('whatify.whatsapp.app_secret') ? '••••••••' : '',
                'verify_token' => config('whatify.whatsapp.verify_token'),
                'webhook_url' => url('/api/webhook/whatsapp'),
            ],
            'payment' => [
                'razorpay_key' => config('services.razorpay.key'),
                'razorpay_secret' => config('services.razorpay.secret') ? '••••••••' : '',
                'cashfree_app_id' => config('services.cashfree.app_id'),
                'cashfree_secret' => config('services.cashfree.secret_key') ? '••••••••' : '',
                'stripe_key' => config('services.stripe.key', env('STRIPE_KEY')),
                'stripe_secret' => env('STRIPE_SECRET') ? '••••••••' : '',
            ],
            'mail' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'username' => config('mail.mailers.smtp.username'),
                'password' => config('mail.mailers.smtp.password') ? '••••••••' : '',
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
            'messaging' => [
                'marketing_cost' => config('whatify.message_cost.marketing'),
                'utility_cost' => config('whatify.message_cost.utility'),
                'authentication_cost' => config('whatify.message_cost.authentication'),
                'service_cost' => config('whatify.message_cost.service'),
                'min_recharge' => config('whatify.wallet.min_recharge'),
                'max_recharge' => config('whatify.wallet.max_recharge'),
                'low_balance_alert' => config('whatify.wallet.low_balance_alert'),
                'default_commission' => config('whatify.partner.default_commission'),
                'min_payout' => config('whatify.partner.min_payout'),
            ],
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|string',
        ]);

        $this->updateEnv([
            'APP_NAME' => $validated['app_name'],
            'APP_URL' => $validated['app_url'],
            'APP_TIMEZONE' => $validated['timezone'],
        ]);

        Artisan::call('config:clear');

        return back()->with('success', 'General settings updated. May require app restart.');
    }

    public function updateWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_api_url' => 'required|url',
            'whatsapp_app_id' => 'nullable|string',
            'whatsapp_app_secret' => 'nullable|string',
            'whatsapp_verify_token' => 'required|string',
        ]);

        $env = [
            'WHATSAPP_API_URL' => $validated['whatsapp_api_url'],
            'WHATSAPP_VERIFY_TOKEN' => $validated['whatsapp_verify_token'],
        ];

        if (!empty($validated['whatsapp_app_id'])) {
            $env['WHATSAPP_APP_ID'] = $validated['whatsapp_app_id'];
        }
        if (!empty($validated['whatsapp_app_secret']) && $validated['whatsapp_app_secret'] !== '••••••••') {
            $env['WHATSAPP_APP_SECRET'] = $validated['whatsapp_app_secret'];
        }

        $this->updateEnv($env);
        Artisan::call('config:clear');

        return back()->with('success', 'WhatsApp settings updated.');
    }

    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
            'cashfree_app_id' => 'nullable|string',
            'cashfree_secret' => 'nullable|string',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
        ]);

        $env = [];

        if (!empty($validated['razorpay_key'])) $env['RAZORPAY_KEY'] = $validated['razorpay_key'];
        if (!empty($validated['razorpay_secret']) && $validated['razorpay_secret'] !== '••••••••') $env['RAZORPAY_SECRET'] = $validated['razorpay_secret'];
        if (!empty($validated['cashfree_app_id'])) $env['CASHFREE_APP_ID'] = $validated['cashfree_app_id'];
        if (!empty($validated['cashfree_secret']) && $validated['cashfree_secret'] !== '••••••••') $env['CASHFREE_SECRET_KEY'] = $validated['cashfree_secret'];
        if (!empty($validated['stripe_key'])) $env['STRIPE_KEY'] = $validated['stripe_key'];
        if (!empty($validated['stripe_secret']) && $validated['stripe_secret'] !== '••••••••') $env['STRIPE_SECRET'] = $validated['stripe_secret'];

        if (!empty($env)) {
            $this->updateEnv($env);
            Artisan::call('config:clear');
        }

        return back()->with('success', 'Payment settings updated.');
    }

    public function updateMail(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|in:tls,ssl,null',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $env = [
            'MAIL_MAILER' => $validated['mail_mailer'],
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'],
            'MAIL_FROM_NAME' => '"' . $validated['mail_from_name'] . '"',
        ];

        if (!empty($validated['mail_host'])) $env['MAIL_HOST'] = $validated['mail_host'];
        if (!empty($validated['mail_port'])) $env['MAIL_PORT'] = $validated['mail_port'];
        if (!empty($validated['mail_username'])) $env['MAIL_USERNAME'] = $validated['mail_username'];
        if (!empty($validated['mail_password']) && $validated['mail_password'] !== '••••••••') $env['MAIL_PASSWORD'] = $validated['mail_password'];
        if (!empty($validated['mail_encryption'])) $env['MAIL_ENCRYPTION'] = $validated['mail_encryption'] === 'null' ? '' : $validated['mail_encryption'];

        $this->updateEnv($env);
        Artisan::call('config:clear');

        return back()->with('success', 'Mail settings updated.');
    }

    public function testMail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);

        try {
            Mail::raw('This is a test email from ' . config('app.name') . '. Your mail configuration is working correctly!', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email - ' . config('app.name'));
            });

            return back()->with('success', "Test email sent to {$request->test_email}");
        } catch (\Exception $e) {
            return back()->with('error', 'Mail test failed: ' . $e->getMessage());
        }
    }

    public function updateMessaging(Request $request)
    {
        $validated = $request->validate([
            'marketing_cost' => 'required|numeric|min:0',
            'utility_cost' => 'required|numeric|min:0',
            'authentication_cost' => 'required|numeric|min:0',
            'service_cost' => 'required|numeric|min:0',
            'min_recharge' => 'required|integer|min:1',
            'max_recharge' => 'required|integer|min:100',
            'low_balance_alert' => 'required|integer|min:0',
            'default_commission' => 'required|numeric|min:0|max:50',
            'min_payout' => 'required|integer|min:100',
        ]);

        $this->updateEnv([
            'MARKETING_MSG_COST' => $validated['marketing_cost'],
            'UTILITY_MSG_COST' => $validated['utility_cost'],
            'AUTH_MSG_COST' => $validated['authentication_cost'],
            'SERVICE_MSG_COST' => $validated['service_cost'],
            'LOW_BALANCE_ALERT' => $validated['low_balance_alert'],
        ]);

        Artisan::call('config:clear');

        return back()->with('success', 'Messaging & pricing settings updated.');
    }

    protected function updateEnv(array $data): void
    {
        $envFile = app()->environmentFilePath();
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            $value = str_contains($value, ' ') && !str_starts_with($value, '"') ? '"' . $value . '"' : $value;

            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}