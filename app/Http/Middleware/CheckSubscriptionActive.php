<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Разрешаем доступ к дашборду и уведомлениям даже с истекшей подпиской
        $allowedRoutes = [
            'client.dashboard',
            'client.notifications',
            'client.notifications.mark-read',
            'client.notifications.mark-all-read',
            'employee.dashboard'
        ];

        if (in_array($request->route()->getName(), $allowedRoutes)) {
            return $next($request);
        }

        // Для клиентов
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();

            if (!$client->isSubscriptionActive()) {
                // Для AJAX запросов
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Подписка истекла',
                        'message' => 'Ваша подписка истекла. Обратитесь к администратору для продления.',
                        'subscription_expired' => true
                    ], 403);
                }

                // Для обычных веб-запросов
                return redirect()->route('client.dashboard')->with(
                    'subscription_expired',
                    'Ваша подписка истекла ' . $client->subscription_end_date->format('d.m.Y') . '. Функциональность ограничена. Обратитесь к администратору для продления.'
                );
            }
        }

        // Для сотрудников
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();
            $client = $employee->client;

            if (!$client->isSubscriptionActive()) {
                // Для AJAX запросов
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Подписка клиента истекла',
                        'message' => 'Подписка вашего клиента истекла. Обратитесь к администратору компании.',
                        'subscription_expired' => true
                    ], 403);
                }

                // Для обычных веб-запросов
                return redirect()->route('employee.dashboard')->with(
                    'subscription_expired',
                    'Подписка вашей компании истекла ' . $client->subscription_end_date->format('d.m.Y') . '. Функциональность ограничена. Обратитесь к администратору компании.'
                );
            }
        }

        return $next($request);
    }
}
