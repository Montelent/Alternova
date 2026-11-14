<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\User;
use App\Support\ApiSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
        $authBucket = 'api-auth:'.$request->ip();
        $authLimit = 10;

        // Authentication failures are rejected before api.throttle runs, so
        // rate-limit missing/invalid credentials here to prevent key guessing.
        if (($plain !== null || ApiSettings::requireKey())
            && RateLimiter::tooManyAttempts($authBucket, $authLimit)
        ) {
            $retry = RateLimiter::availableIn($authBucket);

            return response()->json([
                'error' => 'Too many API authentication attempts. Retry later.',
                'retry_after' => $retry,
            ], 429)->withHeaders(['Retry-After' => (string) $retry]);
        }

        if ($plain === null) {
            if (ApiSettings::requireKey()) {
                RateLimiter::hit($authBucket, 60);

                return response()->json([
                    'error' => 'API key required. Send Authorization: Bearer YOUR_KEY or X-Api-Key.',
                    'code' => 'api_key_required',
                ], 401);
            }

            // The global API switch may permit anonymous access, but an
            // authenticated account that is disabled or has lost API access
            // must not bypass those controls merely by omitting its key.
            $sessionUser = $request->user();
            if ($sessionUser instanceof User && ! $sessionUser->isActive()) {
                return response()->json([
                    'error' => 'This user account is deactivated.',
                    'code' => 'user_inactive',
                ], 403);
            }

            if ($sessionUser instanceof User && ! $sessionUser->hasApiAccess()) {
                return response()->json([
                    'error' => 'API access is disabled for this user.',
                    'code' => 'api_user_disabled',
                ], 403);
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
                RateLimiter::hit($authBucket, 60);

                return response()->json([
                    'error' => 'Invalid or revoked API key.',
                    'code' => 'api_key_invalid',
                ], 401);
            }

            $user = $key->relationLoaded('user') ? $key->user : $key->user()->first();

            // Every issued key belongs to a real account. Never let an orphaned
            // or legacy ownerless key bypass per-user account/API restrictions.
            if (! $user instanceof User) {
                return response()->json([
                    'error' => 'The account associated with this API key is unavailable.',
                    'code' => 'api_key_owner_unavailable',
                ], 403);
            }

            // Make the API-key owner the authenticated request principal so
            // downstream controllers and services cannot accidentally fall
            // back to a different browser-session user (or treat this as a guest).
            $request->setUserResolver(static fn () => $user);
            Auth::setUser($user);

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

            RateLimiter::clear($authBucket);
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

    /**
     * Accept credentials only in headers. Query-string secrets leak into browser
     * history, reverse-proxy/access logs, analytics, and referrer data.
     */
    protected function extractKey(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if (is_string($header) && preg_match('/^Bearer\s+(\S+)$/i', $header, $m)) {
            return $m[1];
        }

        $xKey = $request->header('X-Api-Key');

        return is_string($xKey) && $xKey !== '' ? $xKey : null;
    }
}
