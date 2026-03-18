@extends('layouts.app')
@section('title', 'API Keys')
@section('page-title', 'API Keys')

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- New Key Reveal (only shown once after creation) --}}
    @if(session('new_key'))
        <div class="bg-green-50 border-2 border-green-300 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fas fa-key text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-green-800">API Key Created!</h3>
                    <p class="text-sm text-green-600">Copy these credentials now. The secret will not be shown again.</p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-green-700 mb-1">API Key</label>
                    <div class="flex gap-2">
                        <input type="text" id="newKey" readonly value="{{ session('new_key') }}"
                               class="flex-1 font-mono text-sm bg-white border border-green-300 rounded-lg px-4 py-2 text-green-800">
                        <button onclick="copyToClipboard('newKey')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-green-700 mb-1">API Secret</label>
                    <div class="flex gap-2">
                        <input type="text" id="newSecret" readonly value="{{ session('new_secret') }}"
                               class="flex-1 font-mono text-sm bg-white border border-green-300 rounded-lg px-4 py-2 text-green-800">
                        <button onclick="copyToClipboard('newSecret')" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-xs text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    <strong>Save these credentials securely!</strong> The API Secret cannot be retrieved later. You will need to regenerate if lost.
                </p>
            </div>
        </div>
    @endif

    {{-- Create New Key --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-plus-circle text-emerald-600 mr-2"></i>Create API Key
        </h3>
        <form method="POST" action="{{ route('settings.api-keys.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Key Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Shopify Integration, CRM Sync..."
                           class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Expires After</label>
                    <select name="expires_days" class="mt-1 w-full rounded-lg border-gray-300 border px-4 py-2 text-sm">
                        <option value="">Never Expires</option>
                        <option value="30">30 Days</option>
                        <option value="90">90 Days</option>
                        <option value="180">180 Days</option>
                        <option value="365">1 Year</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach([
                        'send_message' => 'Send Messages',
                        'send_template' => 'Send Templates',
                        'read_contacts' => 'Read Contacts',
                        'write_contacts' => 'Create/Update Contacts',
                        'read_campaigns' => 'Read Campaigns',
                        'read_conversations' => 'Read Conversations',
                        'reply_conversations' => 'Reply to Conversations',
                    ] as $perm => $label)
                        <label class="flex items-center gap-2 p-2 border rounded-lg hover:bg-gray-50 cursor-pointer text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $perm }}" checked
                                   class="rounded border-gray-300 text-emerald-600">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg font-medium hover:bg-emerald-700">
                <i class="fas fa-key mr-2"></i>Generate API Key
            </button>
        </form>
    </div>

    {{-- Existing Keys --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold">Your API Keys</h3>
        </div>

        @if($apiKeys->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <i class="fas fa-key text-4xl mb-3"></i>
                <p>No API keys created yet</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">API Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Used</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($apiKeys as $key)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $key->name }}</div>
                                <div class="text-xs text-gray-400">Created {{ $key->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded font-mono text-gray-600">
                                    {{ substr($key->key, 0, 12) }}...{{ substr($key->key, -4) }}
                                </code>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(($key->permissions ?? []) as $perm)
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">
                                            {{ str_replace('_', ' ', $perm) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $key->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    <span class="mr-1 h-1.5 w-1.5 rounded-full {{ $key->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $key->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($key->expires_at)
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        Expires: {{ $key->expires_at->format('M d, Y') }}
                                        @if($key->expires_at->isPast())
                                            <span class="text-red-500">(Expired)</span>
                                        @endif
                                    </p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-gray-500">
                                {{ $key->last_used_at?->diffForHumans() ?? 'Never' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <form method="POST" action="{{ route('settings.api-keys.toggle', $key) }}">
                                        @csrf
                                        <button class="p-1.5 text-gray-400 hover:text-yellow-600" title="{{ $key->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $key->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.api-keys.regenerate', $key) }}" onsubmit="return confirm('Regenerate this key? Old key will stop working.')">
                                        @csrf
                                        <button class="p-1.5 text-gray-400 hover:text-blue-600" title="Regenerate"><i class="fas fa-sync"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('settings.api-keys.destroy', $key) }}" onsubmit="return confirm('Delete this API key permanently?')">
                                        @csrf @method('DELETE')
                                        <button class="p-1.5 text-gray-400 hover:text-red-600" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Quick API Reference --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-book text-blue-600 mr-2"></i>Quick API Reference
        </h3>
        <div class="bg-gray-900 rounded-lg p-4 text-sm font-mono text-gray-100 overflow-x-auto">
            <p class="text-gray-400"># Base URL</p>
            <p class="text-green-400">{{ url('/api/v1/external') }}</p>
            <br>
            <p class="text-gray-400"># Authentication Header</p>
            <p>X-API-Key: <span class="text-yellow-300">your_api_key_here</span></p>
            <br>
            <p class="text-gray-400"># Send Message</p>
            <p><span class="text-blue-400">POST</span> /send-message</p>
            <p class="text-gray-500">Content-Type: application/json</p>
            <p class="text-gray-300">{"phone": "919876543210", "message": "Hello!"}</p>
            <br>
            <p class="text-gray-400"># Send Template</p>
            <p><span class="text-blue-400">POST</span> /send-template</p>
            <p class="text-gray-300">{"phone": "919876543210", "template_name": "order_update", "body_params": ["John", "#1234"]}</p>
            <br>
            <p class="text-gray-400"># Create Contact</p>
            <p><span class="text-blue-400">POST</span> /contacts</p>
            <p class="text-gray-300">{"phone": "919876543210", "name": "John", "tags": ["vip"]}</p>
            <br>
            <p class="text-gray-400"># List Contacts</p>
            <p><span class="text-green-400">GET</span> /contacts?phone=9876&tag=vip</p>
            <br>
            <p class="text-gray-400"># Check Balance</p>
            <p><span class="text-green-400">GET</span> /wallet/balance</p>
            <br>
            <p class="text-gray-400"># Test Connection</p>
            <p><span class="text-green-400">GET</span> /ping</p>
        </div>

        <div class="mt-4 flex gap-3">
            <a href="{{ route('website.api-docs') }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                <i class="fas fa-book-open mr-1"></i> Full API Documentation
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const input = document.getElementById(elementId);
    input.select();
    navigator.clipboard.writeText(input.value);

    const btn = input.nextElementSibling;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.replace('bg-green-600', 'bg-gray-600');
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.replace('bg-gray-600', 'bg-green-600');
    }, 2000);
}
</script>
@endpush
@endsection