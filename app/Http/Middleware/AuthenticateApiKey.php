<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\User;
use App\Support\ApiSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Master switch — admin can turn the entire public API off
        if (! ApiSettings::isGloballyEnabled()) {
            return response()->json([
                'error' => 'The public API is disabled on this site.',
                'code' => 'api_disabled',
            ], 503);
        }

        $plain = $this->extractKey($request);

        if ($plain === null) {
            if (ApiSettings::requireKey()) {
                return response()->json([
                    'error' => 'API key required. Send Authorization: Bearer YOUR_KEY or X-Api-Key.',
                    'code' => 'api_key_required',
                ], 401);
            }

            return $next($request);
        }

        try {
            if (! Schema::hasTable('api_keys')) {
                return response()->json([
                    'error' => 'API keys are not available on this install.',
                    'code' => 'api_unavailable',
                ], 503);
            }

            $key = ApiKey::findByPlainKey($plain);
            if (! $key) {
                return response()->json([
                    'error' => 'Invalid or revoked API key.',
                    'code' => 'api_key_invalid',
                ], 401);
            }

            $user = $key->relationLoaded('user') ? $key->user : $key->user()->first();

            if ($user instanceof User) {
                if (! $user->isActive()) {
                    return response()->json([
                        'error' => 'This user account is deactivated.',
                        'code' => 'user_inactive',
                    ], 403);
                }

                if (! $user->hasApiAccess()) {
                    return response()->json([
                        'error' => 'API access is disabled for this user.',
                        'code' => 'api_user_disabled',
                    ], 403);
                }
            }

            $key->markUsed();
            $request->attributes->set('api_key', $key);
            $request->attributes->set('api_key_user_id', $key->user_id);
            if ($user) {
                $request->attributes->set('api_key_user', $user);
            }
        } catch (\Throwable) {
            // Table missing or DB error — deny when a key was presented
            return response()->json([
                'error' => 'Unable to validate API key.',
                'code' => 'api_error',
            ], 503);
        }

        return $next($request);
    }

    protected function extractKey(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (is_string($header) && preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            return $m[1];
        }

        $xKey = $request->header('X-Api-Key');
        if (is_string($xKey) && $xKey !== '') {
            return $xKey;
        }

        $query = $request->query('api_key');
        if (is_string($query) && $query !== '') {
            return $query;
        }

        return null;
    }
}
