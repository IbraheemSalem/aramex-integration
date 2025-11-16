<?php

namespace Ibraheem\AramexIntegration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ibraheem\AramexIntegration\Models\MerchantAramexAccount;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY') 
            ?? $request->input('api_key')
            ?? $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required',
                'error_code' => 'ARAMEX_API_KEY_MISSING',
            ], 401);
        }

        $account = MerchantAramexAccount::findByApiKey($apiKey);

        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key',
                'error_code' => 'ARAMEX_API_KEY_INVALID',
            ], 401);
        }

        if (!$account->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active',
                'error_code' => 'ARAMEX_ACCOUNT_INACTIVE',
            ], 403);
        }

        // Attach merchant ID to request
        $request->merge(['merchant_id' => $account->merchant_id]);
        $request->setUserResolver(function () use ($account) {
            return $account;
        });

        return $next($request);
    }
}

