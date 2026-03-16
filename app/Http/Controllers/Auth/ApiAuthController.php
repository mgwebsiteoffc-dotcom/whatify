<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    /**
     * Mobile App Login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required|string',
            'device_token' => 'nullable|string', // FCM token
            'platform' => 'nullable|in:ios,android,web',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'message' => 'Account suspended',
            ], 403);
        }

        // Create sanctum token
        $token = $user->createToken($validated['device_name'])->plainTextToken;

        // Register device token for push notifications
        if (!empty($validated['device_token'])) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $validated['device_token']],
                [
                    'platform' => $validated['platform'] ?? 'android',
                    'is_active' => true,
                ]
            );
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * Mobile App Logout
     */
    public function logout(Request $request)
    {
        // Deactivate device token
        if ($request->device_token) {
            $request->user()->deviceTokens()
                ->where('token', $request->device_token)
                ->update(['is_active' => false]);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    protected function formatUser(User $user): array
    {
        $user->load(['business', 'wallet']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'business' => $user->business ? [
                'company_name' => $user->business->company_name,
                'industry' => $user->business->industry,
                'logo' => $user->business->logo ? asset('storage/' . $user->business->logo) : null,
            ] : null,
            'wallet_balance' => $user->wallet?->balance ?? 0,
            'is_onboarded' => $user->is_onboarded,
            'unread_notifications' => $user->notifications()->where('is_read', false)->count(),
        ];
    }
}