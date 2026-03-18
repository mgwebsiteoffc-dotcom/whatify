<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): mixed
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required.',
                'message' => 'Provide X-API-Key header or api_key query parameter.',
            ], 401);
        }

        $keyRecord = ApiKey::where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$keyRecord) {
            return response()->json([
                'error' => 'Invalid API key.',
            ], 401);
        }

        if ($keyRecord->expires_at && $keyRecord->expires_at->isPast()) {
            return response()->json([
                'error' => 'API key has expired.',
            ], 401);
        }

        $keyRecord->update(['last_used_at' => now()]);

        $request->merge(['api_key_user_id' => $keyRecord->user_id]);
        $request->merge(['api_key_permissions' => $keyRecord->permissions ?? []]);
        $request->merge(['api_key_record' => $keyRecord]);

        auth()->loginUsingId($keyRecord->user_id);

        return $next($request);
    }
}