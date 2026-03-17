@extends('layouts.app')
@section('title', 'Connect WhatsApp')
@section('page-title', 'Connect WhatsApp Number')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Option 1: Embedded Signup (Facebook Login) --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2">Option 1: Quick Setup (Recommended)</h3>
        <p class="text-sm text-gray-500 mb-4">Sign in with Facebook to automatically connect your WhatsApp Business account.</p>

        @if($appId)
            <div id="fb-root"></div>
            <button onclick="launchWhatsAppSignup()" class="w-full px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium text-sm">
                <i class="fab fa-facebook mr-2"></i> Continue with Facebook
            </button>
            <p class="text-xs text-gray-400 mt-2 text-center">This will open Facebook login to connect your WhatsApp Business API</p>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm text-yellow-700">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Facebook App ID not configured. Please ask your admin to set up WhatsApp API credentials in
                <a href="{{ route('admin.settings.index') }}" class="underline">Admin Settings</a>.
            </div>
        @endif
    </div>

    <div class="text-center text-sm text-gray-400">— OR —</div>

    {{-- Option 2: Manual Setup --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-2">Option 2: Manual Setup</h3>
        <p class="text-sm text-gray-500 mb-4">Enter your WhatsApp Business API credentials manually.</p>
        <a href="{{ route('whatsapp.accounts.create') }}" class="inline-flex items-center px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            <i class="fas fa-keyboard mr-2"></i> Enter Credentials Manually
        </a>
    </div>

    {{-- Setup Guide --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Setup Guide</h3>

        <div class="space-y-4">
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">1</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Create Facebook Business Account</p>
                    <p class="text-xs text-gray-500">Go to <a href="https://business.facebook.com" target="_blank" class="text-blue-600 underline">business.facebook.com</a> and set up your business.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">2</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Verify Your Business</p>
                    <p class="text-xs text-gray-500">Complete business verification in Meta Business Suite with your documents.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">3</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Add WhatsApp Number</p>
                    <p class="text-xs text-gray-500">In WhatsApp Manager, add and verify your business phone number.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">4</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Generate API Token</p>
                    <p class="text-xs text-gray-500">Create a System User and generate a permanent access token with <code class="bg-gray-100 px-1 rounded">whatsapp_business_messaging</code> permission.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">5</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Configure Webhook</p>
                    <p class="text-xs text-gray-500">
                        Set webhook URL to: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ url('/api/webhook/whatsapp') }}</code><br>
                        Verify Token: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">{{ config('whatify.whatsapp.verify_token') }}</code><br>
                        Subscribe to: <code class="bg-gray-100 px-2 py-0.5 rounded text-xs">messages</code>
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center text-sm font-bold text-emerald-700 flex-shrink-0">6</div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Connect on Whatify</p>
                    <p class="text-xs text-gray-500">Enter your Phone Number ID, WABA ID and Access Token to complete setup.</p>
                </div>
            </div>
        </div>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm font-medium text-gray-700 mb-2">Need help finding your credentials?</p>
            <div class="text-xs text-gray-500 space-y-1">
                <p><strong>Phone Number ID:</strong> WhatsApp Manager → Phone Numbers → Click your number → "Phone number ID" field</p>
                <p><strong>WABA ID:</strong> WhatsApp Manager → Account → "WhatsApp Business Account ID" field</p>
                <p><strong>Access Token:</strong> Business Settings → System Users → Select user → Generate Token → Select whatsapp_business_messaging permission</p>
            </div>
        </div>
    </div>
</div>

@if($appId)
@push('scripts')
<script>
    window.fbAsyncInit = function() {
        FB.init({
            appId: '{{ $appId }}',
            autoLogAppEvents: true,
            xfbml: true,
            version: 'v18.0'
        });
    };

    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "https://connect.facebook.net/en_US/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function launchWhatsAppSignup() {
        FB.login(function(response) {
            if (response.authResponse) {
                const code = response.authResponse.code;

                // Send code to backend
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("whatsapp.accounts.embeddedSignupCallback") }}';

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const codeInput = document.createElement('input');
                codeInput.type = 'hidden';
                codeInput.name = 'code';
                codeInput.value = code;
                form.appendChild(codeInput);

                document.body.appendChild(form);
                form.submit();
            }
        }, {
            config_id: '{{ $configId }}',
            response_type: 'code',
            override_default_response_type: true,
            extras: {
                setup: {},
                featureType: '',
                sessionInfoVersion: '2',
            }
        });
    }
</script>
@endpush
@endif
@endsection