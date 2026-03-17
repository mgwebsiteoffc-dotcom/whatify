@extends('layouts.app')
@section('title', 'Platform Settings')
@section('page-title', 'Platform Settings')

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ activeTab: 'general' }">

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-lg shadow">
        <div class="border-b flex overflow-x-auto">
            @foreach([
                ['general', 'General', 'fas fa-cog'],
                ['whatsapp', 'WhatsApp API', 'fab fa-whatsapp'],
                ['payment', 'Payment Gateways', 'fas fa-credit-card'],
                ['mail', 'Email / SMTP', 'fas fa-envelope'],
                ['messaging', 'Pricing & Limits', 'fas fa-rupee-sign'],
            ] as [$tab, $label, $icon])
                <button @click="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'border-emerald-500 text-emerald-600 bg-emerald-50' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 whitespace-nowrap">
                    <i class="{{ $icon }}"></i> {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- General Settings --}}
    <div x-show="activeTab === 'general'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">General Settings</h3>
            <form method="POST" action="{{ route('admin.settings.general') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application Name</label>
                        <input type="text" name="app_name" value="{{ $settings['general']['app_name'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Application URL</label>
                        <input type="url" name="app_url" value="{{ $settings['general']['app_url'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Timezone</label>
                        <select name="timezone" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            @foreach(['Asia/Kolkata', 'UTC', 'America/New_York', 'Europe/London', 'Asia/Dubai'] as $tz)
                                <option value="{{ $tz }}" {{ $settings['general']['timezone'] === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Save General Settings</button>
            </form>
        </div>
    </div>

    {{-- WhatsApp Settings --}}
    <div x-show="activeTab === 'whatsapp'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">WhatsApp Business API Settings</h3>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800 font-medium"><i class="fas fa-info-circle mr-1"></i> Webhook Configuration</p>
                <div class="mt-2 space-y-1 text-sm text-blue-700">
                    <p><strong>Callback URL:</strong> <code class="bg-blue-100 px-2 py-0.5 rounded">{{ $settings['whatsapp']['webhook_url'] }}</code></p>
                    <p><strong>Verify Token:</strong> <code class="bg-blue-100 px-2 py-0.5 rounded">{{ $settings['whatsapp']['verify_token'] }}</code></p>
                    <p><strong>Subscribed Fields:</strong> <code class="bg-blue-100 px-2 py-0.5 rounded">messages</code></p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.whatsapp') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">API URL</label>
                        <input type="url" name="whatsapp_api_url" value="{{ $settings['whatsapp']['api_url'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Verify Token</label>
                        <input type="text" name="whatsapp_verify_token" value="{{ $settings['whatsapp']['verify_token'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta App ID</label>
                        <input type="text" name="whatsapp_app_id" value="{{ $settings['whatsapp']['app_id'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Meta App Secret</label>
                        <input type="text" name="whatsapp_app_secret" value="{{ $settings['whatsapp']['app_secret'] }}" placeholder="Leave empty to keep current"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                    </div>
                </div>
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Save WhatsApp Settings</button>
            </form>
        </div>
    </div>

    {{-- Payment Settings --}}
    <div x-show="activeTab === 'payment'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Payment Gateway Settings</h3>
            <form method="POST" action="{{ route('admin.settings.payment') }}" class="space-y-6">
                @csrf

                {{-- Razorpay --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3"><i class="fas fa-bolt text-blue-500 mr-1"></i> Razorpay</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500">Key ID</label>
                            <input type="text" name="razorpay_key" value="{{ $settings['payment']['razorpay_key'] }}"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono" placeholder="rzp_live_xxx">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Key Secret</label>
                            <input type="text" name="razorpay_secret" value="{{ $settings['payment']['razorpay_secret'] }}" placeholder="Leave empty to keep current"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                    </div>
                </div>

                {{-- Cashfree --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3"><i class="fas fa-money-bill text-green-500 mr-1"></i> Cashfree</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500">App ID</label>
                            <input type="text" name="cashfree_app_id" value="{{ $settings['payment']['cashfree_app_id'] }}"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Secret Key</label>
                            <input type="text" name="cashfree_secret" value="{{ $settings['payment']['cashfree_secret'] }}" placeholder="Leave empty to keep current"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                    </div>
                </div>

                {{-- Stripe --}}
                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3"><i class="fab fa-stripe text-indigo-500 mr-1"></i> Stripe</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500">Publishable Key</label>
                            <input type="text" name="stripe_key" value="{{ $settings['payment']['stripe_key'] }}"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono" placeholder="pk_live_xxx">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Secret Key</label>
                            <input type="text" name="stripe_secret" value="{{ $settings['payment']['stripe_secret'] }}" placeholder="Leave empty to keep current"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border font-mono">
                        </div>
                    </div>
                </div>

                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Save Payment Settings</button>
            </form>
        </div>
    </div>

    {{-- Mail Settings --}}
    <div x-show="activeTab === 'mail'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Email / SMTP Settings</h3>
            <form method="POST" action="{{ route('admin.settings.mail') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mail Driver</label>
                        <select name="mail_mailer" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            @foreach(['smtp', 'sendmail', 'mailgun', 'ses', 'postmark', 'log'] as $driver)
                                <option value="{{ $driver }}" {{ $settings['mail']['mailer'] === $driver ? 'selected' : '' }}>{{ strtoupper($driver) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Encryption</label>
                        <select name="mail_encryption" class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                            <option value="tls" {{ $settings['mail']['encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $settings['mail']['encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="null" {{ empty($settings['mail']['encryption']) ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                        <input type="text" name="mail_host" value="{{ $settings['mail']['host'] }}" placeholder="smtp.gmail.com"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                        <input type="number" name="mail_port" value="{{ $settings['mail']['port'] }}" placeholder="587"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                        <input type="text" name="mail_username" value="{{ $settings['mail']['username'] }}"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                        <input type="text" name="mail_password" value="{{ $settings['mail']['password'] }}" placeholder="Leave empty to keep current"
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Email</label>
                        <input type="email" name="mail_from_address" value="{{ $settings['mail']['from_address'] }}" required
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">From Name</label>
                        <input type="text" name="mail_from_name" value="{{ $settings['mail']['from_name'] }}" required
                               class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Save Mail Settings</button>
                </div>
            </form>

            {{-- Test Email --}}
            <div class="mt-6 pt-6 border-t">
                <h4 class="text-sm font-semibold mb-3">Test Email Configuration</h4>
                <form method="POST" action="{{ route('admin.settings.testMail') }}" class="flex gap-3">
                    @csrf
                    <input type="email" name="test_email" required placeholder="test@example.com"
                           class="flex-1 rounded-md border-gray-300 text-sm px-3 py-2 border">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                        <i class="fas fa-paper-plane mr-1"></i> Send Test Email
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Messaging & Pricing Settings --}}
    <div x-show="activeTab === 'messaging'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Message Pricing & Platform Limits</h3>
            <form method="POST" action="{{ route('admin.settings.messaging') }}" class="space-y-6">
                @csrf

                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Message Cost Per Category (₹)</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach([
                            ['marketing_cost', 'Marketing', $settings['messaging']['marketing_cost']],
                            ['utility_cost', 'Utility', $settings['messaging']['utility_cost']],
                            ['authentication_cost', 'Authentication', $settings['messaging']['authentication_cost']],
                            ['service_cost', 'Service', $settings['messaging']['service_cost']],
                        ] as [$field, $label, $value])
                            <div>
                                <label class="block text-xs text-gray-500">{{ $label }}</label>
                                <div class="relative mt-1">
                                    <span class="absolute left-3 top-2 text-gray-400 text-sm">₹</span>
                                    <input type="number" name="{{ $field }}" value="{{ $value }}" step="0.01" min="0"
                                           class="w-full pl-7 rounded-md border-gray-300 text-sm px-3 py-2 border">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Wallet Settings</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500">Min Recharge (₹)</label>
                            <input type="number" name="min_recharge" value="{{ $settings['messaging']['min_recharge'] }}" min="1"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Max Recharge (₹)</label>
                            <input type="number" name="max_recharge" value="{{ $settings['messaging']['max_recharge'] }}" min="100"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Low Balance Alert (₹)</label>
                            <input type="number" name="low_balance_alert" value="{{ $settings['messaging']['low_balance_alert'] }}" min="0"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </div>

                <div class="border rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Partner Settings</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500">Default Commission (%)</label>
                            <input type="number" name="default_commission" value="{{ $settings['messaging']['default_commission'] }}" min="0" max="50" step="0.5"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500">Min Payout (₹)</label>
                            <input type="number" name="min_payout" value="{{ $settings['messaging']['min_payout'] }}" min="100"
                                   class="mt-1 w-full rounded-md border-gray-300 text-sm px-3 py-2 border">
                        </div>
                    </div>
                </div>

                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 text-sm">Save Pricing & Limits</button>
            </form>
        </div>
    </div>
</div>
@endsection