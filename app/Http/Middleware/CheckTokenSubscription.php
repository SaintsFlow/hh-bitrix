<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenSubscription
{
    /**
     * Handle an incoming request for API with Sanctum tokens.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && method_exists($user, 'client')) {
            $client = $user->client;

            if (!$client->isSubscriptionActive()) {
                return response()->json([
                    'error' => 'Subscription expired',
                    'message' => 'Your company subscription has expired. Please contact your administrator.',
                    'subscription_expired' => true,
                    'subscription_end_date' => $client->subscription_end_date->format('Y-m-d'),
                    'code' => 'SUBSCRIPTION_EXPIRED'
                ], 403);
            }
        }

        return $next($request);
    }
}
