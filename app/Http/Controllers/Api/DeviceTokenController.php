<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'platform' => 'required|in:ios,android,web',
        ]);

        $request->user()->deviceTokens()->updateOrCreate(
            ['token' => $validated['token']],
            [
                'platform' => $validated['platform'],
                'is_active' => true,
            ]
        );

        return response()->json(['message' => 'Token registered']);
    }

    public function destroy(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $request->user()->deviceTokens()
            ->where('token', $request->token)
            ->update(['is_active' => false]);

        return response()->json(['message' => 'Token removed']);
    }
}