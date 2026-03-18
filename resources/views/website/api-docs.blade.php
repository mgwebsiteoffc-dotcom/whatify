@extends('website.layouts.app')

@section('title', 'API Documentation - Whatify WhatsApp API')
@section('meta_description', 'Whatify API documentation. Send WhatsApp messages, manage contacts, and automate campaigns via REST API.')

@section('content')
<div class="bg-gray-900 text-gray-100">
    <div class="max-w-5xl mx-auto px-4 py-16">

        <div class="mb-12">
            <h1 class="text-4xl font-bold text-white">Whatify API Documentation</h1>
            <p class="mt-4 text-lg text-gray-400">Integrate WhatsApp messaging into your application using our REST API.</p>
            <div class="mt-4 flex gap-3">
                <span class="px-3 py-1 bg-green-900 text-green-300 rounded-full text-xs font-medium">v1.0</span>
                <span class="px-3 py-1 bg-blue-900 text-blue-300 rounded-full text-xs font-medium">REST API</span>
                <span class="px-3 py-1 bg-purple-900 text-purple-300 rounded-full text-xs font-medium">JSON</span>
            </div>
        </div>

        {{-- Base URL --}}
        <div class="mb-12 p-6 bg-gray-800 rounded-xl">
            <h2 class="text-xl font-bold text-white mb-3">Base URL</h2>
            <code class="text-green-400 text-lg">{{ url('/api/v1/external') }}</code>
        </div>

        {{-- Authentication --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-white mb-4">Authentication</h2>
            <p class="text-gray-400 mb-4">All API requests require an API Key sent via the <code class="text-yellow-300 bg-gray-800 px-2 py-0.5 rounded">X-API-Key</code> header.</p>
            <div class="bg-gray-800 rounded-xl p-6 font-mono text-sm">
                <p class="text-gray-500"># Example request</p>
                <p><span class="text-blue-400">curl</span> -X POST {{ url('/api/v1/external/send-message') }} \</p>
                <p class="pl-4">-H <span class="text-yellow-300">"X-API-Key: wfy_your_api_key_here"</span> \</p>
                <p class="pl-4">-H <span class="text-yellow-300">"Content-Type: application/json"</span> \</p>
                <p class="pl-4">-d <span class="text-green-300">'{"phone": "919876543210", "message": "Hello from API!"}'</span></p>
            </div>
            <p class="text-gray-500 text-sm mt-3">
                Generate your API key from <strong>Settings → API Keys</strong> in your dashboard.
                <a href="{{ route('register') }}" class="text-emerald-400 underline">Create free account →</a>
            </p>
        </div>

        {{-- Endpoints --}}
        @foreach([
            [
                'section' => 'Messages',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/send-message',
                        'title' => 'Send Text Message',
                        'desc' => 'Send a plain text WhatsApp message to a phone number.',
                        'body' => '{"phone": "919876543210", "message": "Hello! Your order is confirmed.", "whatsapp_account_id": 1}',
                        'response' => '{"success": true, "message_id": 505, "status": "queued", "contact_id": 123}',
                        'params' => [
                            ['phone', 'string', 'Yes', 'Full phone with country code'],
                            ['message', 'string', 'Yes', 'Message text (max 4096 chars)'],
                            ['whatsapp_account_id', 'int', 'No', 'Specific WA account ID'],
                        ],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/send-template',
                        'title' => 'Send Template Message',
                        'desc' => 'Send an approved template message with dynamic parameters.',
                        'body' => '{"phone": "919876543210", "template_name": "order_confirmation", "body_params": ["John", "ORD-1234"]}',
                        'response' => '{"success": true, "message_id": 506, "status": "queued"}',
                        'params' => [
                            ['phone', 'string', 'Yes', 'Full phone with country code'],
                            ['template_name', 'string', 'Yes', 'Approved template name'],
                            ['body_params', 'array', 'No', 'Template variable values'],
                            ['header_params', 'array', 'No', 'Header variable values'],
                        ],
                    ],
                ],
            ],
            [
                'section' => 'Contacts',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/contacts',
                        'title' => 'List Contacts',
                        'desc' => 'Retrieve paginated list of contacts with optional filters.',
                        'body' => null,
                        'response' => '{"current_page": 1, "data": [{"id": 1, "name": "John", "phone": "9876543210"}], "total": 100}',
                        'params' => [
                            ['phone', 'string', 'No', 'Search by phone'],
                            ['name', 'string', 'No', 'Search by name'],
                            ['tag', 'string', 'No', 'Filter by tag name'],
                            ['status', 'string', 'No', 'active, inactive, blocked'],
                            ['per_page', 'int', 'No', 'Items per page (default 50)'],
                        ],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/contacts',
                        'title' => 'Create Contact',
                        'desc' => 'Create a new contact or update existing one by phone number.',
                        'body' => '{"phone": "919876543210", "name": "Jane", "email": "jane@example.com", "tags": ["vip", "customer"], "custom_attributes": {"city": "Mumbai"}}',
                        'response' => '{"success": true, "contact": {"id": 124, "name": "Jane", "phone": "9876543210"}, "created": true}',
                        'params' => [
                            ['phone', 'string', 'Yes', 'Phone number'],
                            ['name', 'string', 'No', 'Contact name'],
                            ['email', 'string', 'No', 'Email address'],
                            ['tags', 'array', 'No', 'Tag names (auto-created)'],
                            ['custom_attributes', 'object', 'No', 'Custom key-value data'],
                        ],
                    ],
                ],
            ],
            [
                'section' => 'Other Endpoints',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/templates',
                        'title' => 'List Templates',
                        'desc' => 'Get all message templates with their status.',
                        'body' => null,
                        'response' => '{"templates": [{"id": 1, "name": "order_update", "status": "approved", "category": "utility"}]}',
                        'params' => [
                            ['status', 'string', 'No', 'approved, pending, rejected'],
                            ['category', 'string', 'No', 'marketing, utility, authentication'],
                        ],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/wallet/balance',
                        'title' => 'Check Wallet Balance',
                        'desc' => 'Get current wallet balance and spending stats.',
                        'body' => null,
                        'response' => '{"balance": 1500.50, "currency": "INR", "total_recharged": 10000, "total_spent": 8499.50}',
                        'params' => [],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/ping',
                        'title' => 'Test Connection',
                        'desc' => 'Verify your API key is working.',
                        'body' => null,
                        'response' => '{"status": "ok", "timestamp": "2024-03-17T10:30:00Z"}',
                        'params' => [],
                    ],
                ],
            ],
        ] as $section)
            <div class="mb-16">
                <h2 class="text-2xl font-bold text-white mb-6 pb-2 border-b border-gray-700">{{ $section['section'] }}</h2>

                @foreach($section['endpoints'] as $endpoint)
                    <div class="mb-10" id="{{ \Illuminate\Support\Str::slug($endpoint['title']) }}">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase
                                {{ $endpoint['method'] === 'GET' ? 'bg-green-900 text-green-300' : 'bg-blue-900 text-blue-300' }}">
                                {{ $endpoint['method'] }}
                            </span>
                            <code class="text-lg text-yellow-300">{{ $endpoint['path'] }}</code>
                        </div>
                        <h3 class="text-xl font-semibold text-white">{{ $endpoint['title'] }}</h3>
                        <p class="text-gray-400 mt-1">{{ $endpoint['desc'] }}</p>

                        @if(!empty($endpoint['params']))
                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="text-left border-b border-gray-700">
                                            <th class="py-2 pr-4 text-gray-500">Parameter</th>
                                            <th class="py-2 pr-4 text-gray-500">Type</th>
                                            <th class="py-2 pr-4 text-gray-500">Required</th>
                                            <th class="py-2 text-gray-500">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($endpoint['params'] as [$param, $type, $required, $desc])
                                            <tr class="border-b border-gray-800">
                                                <td class="py-2 pr-4"><code class="text-yellow-300">{{ $param }}</code></td>
                                                <td class="py-2 pr-4 text-purple-300">{{ $type }}</td>
                                                <td class="py-2 pr-4 {{ $required === 'Yes' ? 'text-red-400' : 'text-gray-500' }}">{{ $required }}</td>
                                                <td class="py-2 text-gray-400">{{ $desc }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($endpoint['body'])
                            <div class="mt-4">
                                <p class="text-xs text-gray-500 mb-1">Request Body:</p>
                                <pre class="bg-gray-800 rounded-lg p-4 text-sm text-green-300 overflow-x-auto"><code>{{ json_encode(json_decode($endpoint['body']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                            </div>
                        @endif

                        <div class="mt-3">
                            <p class="text-xs text-gray-500 mb-1">Response:</p>
                            <pre class="bg-gray-800 rounded-lg p-4 text-sm text-blue-300 overflow-x-auto"><code>{{ json_encode(json_decode($endpoint['response']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        {{-- Error Codes --}}
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-white mb-6 pb-2 border-b border-gray-700">Error Codes</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-700">
                            <th class="py-2 pr-4 text-gray-500">Code</th>
                            <th class="py-2 pr-4 text-gray-500">Status</th>
                            <th class="py-2 text-gray-500">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['200', 'OK', 'Request successful'],
                            ['201', 'Created', 'Resource created successfully'],
                            ['401', 'Unauthorized', 'Invalid or missing API key'],
                            ['403', 'Forbidden', 'Permission denied for this action'],
                            ['404', 'Not Found', 'Resource not found'],
                            ['422', 'Unprocessable', 'Validation error or insufficient balance'],
                            ['429', 'Too Many Requests', 'Rate limit exceeded (60/min)'],
                            ['500', 'Server Error', 'Internal server error'],
                        ] as [$code, $status, $desc])
                            <tr class="border-b border-gray-800">
                                <td class="py-2 pr-4"><code class="text-yellow-300">{{ $code }}</code></td>
                                <td class="py-2 pr-4 text-white">{{ $status }}</td>
                                <td class="py-2 text-gray-400">{{ $desc }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rate Limits --}}
        <div class="mb-16">
            <h2 class="text-2xl font-bold text-white mb-4">Rate Limits</h2>
            <p class="text-gray-400">API requests are limited to <strong class="text-white">60 requests per minute</strong> per API key. Message sending is limited to <strong class="text-white">100 messages per minute</strong>.</p>
        </div>

        {{-- CTA --}}
        <div class="bg-emerald-900 rounded-xl p-8 text-center">
            <h3 class="text-2xl font-bold text-white mb-3">Ready to Integrate?</h3>
            <p class="text-emerald-200 mb-6">Create your free account and generate an API key in minutes.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-500">
                Start Free Trial <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
@endsection