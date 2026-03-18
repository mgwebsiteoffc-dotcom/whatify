<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index()
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $apiKeys = ApiKey::where('user_id', $owner->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('settings.api-keys', compact('apiKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:send_message,send_template,read_contacts,write_contacts,read_campaigns,read_conversations,reply_conversations',
            'expires_days' => 'nullable|integer|min:0|max:365',
        ]);

        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();

        $key = ApiKey::generateKey();
        $secret = ApiKey::generateSecret();

        $apiKey = ApiKey::create([
            'user_id' => $owner->id,
            'name' => $validated['name'],
            'key' => $key,
            'secret' => hash('sha256', $secret),
            'permissions' => $validated['permissions'] ?? [
                'send_message', 'send_template', 'read_contacts',
                'write_contacts', 'read_campaigns', 'read_conversations',
            ],
            'expires_at' => !empty($validated['expires_days'])
                ? now()->addDays($validated['expires_days'])
                : null,
            'is_active' => true,
        ]);

        return redirect()->route('settings.api-keys')
            ->with('success', 'API Key created successfully!')
            ->with('new_key', $key)
            ->with('new_secret', $secret)
            ->with('key_id', $apiKey->id);
    }

    public function toggleStatus(ApiKey $apiKey)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($apiKey->user_id !== $owner->id, 403);

        $apiKey->update(['is_active' => !$apiKey->is_active]);

        $status = $apiKey->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "API Key {$status}.");
    }

    public function destroy(ApiKey $apiKey)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($apiKey->user_id !== $owner->id, 403);

        $apiKey->delete();

        return back()->with('success', 'API Key deleted.');
    }

    public function regenerate(ApiKey $apiKey)
    {
        $owner = auth()->user()->getBusinessOwner() ?? auth()->user();
        abort_if($apiKey->user_id !== $owner->id, 403);

        $newKey = ApiKey::generateKey();
        $newSecret = ApiKey::generateSecret();

        $apiKey->update([
            'key' => $newKey,
            'secret' => hash('sha256', $newSecret),
        ]);

        return redirect()->route('settings.api-keys')
            ->with('success', 'API Key regenerated!')
            ->with('new_key', $newKey)
            ->with('new_secret', $newSecret)
            ->with('key_id', $apiKey->id);
    }
}