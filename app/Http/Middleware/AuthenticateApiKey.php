<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $this->extractKey($request);

        if ($plain === null) {
            return $next($request);
        }

        try {
            if (! Schema::hasTable('api_keys')) {
                return $next($request);
            }

            $key = ApiKey::findByPlainKey($plain);
            if (! $key) {
                return response()->json([
                    'error' => 'Invalid or revoked API key.',
                ], 401);
            }

            $key->markUsed();
            $request->attributes->set('api_key', $key);
            $request->attributes->set('api_key_user_id', $key->user_id);
        } catch (\Throwable) {
            // Table missing or DB error — treat as anonymous
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
