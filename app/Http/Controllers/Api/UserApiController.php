<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Получить данные текущего пользователя
     */
    public function getUser(Request $request)
    {
        $employee = $request->user();

        // Логируем запрос данных пользователя через API
        activity()
            ->performedOn($employee)
            ->causedBy($employee)
            ->withProperties([
                'token_name' => $request->user()->currentAccessToken()->name,
                'via_api' => true
            ])
            ->log('Запрошены данные пользователя через API');

        return $employee;
    }

    /**
     * Получить профиль пользователя
     */
    public function getProfile(Request $request)
    {
        $employee = $request->user();
        $client = $employee->client;

        // Логируем запрос профиля через API
        activity()
            ->performedOn($employee)
            ->causedBy($employee)
            ->withProperties([
                'token_name' => $request->user()->currentAccessToken()->name,
                'via_api' => true
            ])
            ->log('Запрошен профиль через API');

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'created_at' => $employee->created_at,
                'last_login_at' => $employee->last_login_at,
            ],
            'company' => [
                'name' => $client->name,
                'subscription_end_date' => $client->subscription_end_date,
                'is_subscription_active' => $client->isSubscriptionActive(),
            ]
        ]);
    }

    /**
     * Обновить профиль пользователя
     */
    public function updateProfile(Request $request)
    {
        $employee = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $employee->id,
        ]);

        // Сохраняем старые данные для логирования
        $oldData = $employee->only(['name', 'email']);

        $employee->update($request->only(['name', 'email']));

        // Логируем обновление профиля через API
        activity()
            ->performedOn($employee)
            ->causedBy($employee)
            ->withProperties([
                'old' => $oldData,
                'attributes' => $request->only(['name', 'email']),
                'token_name' => $request->user()->currentAccessToken()->name,
                'via_api' => true
            ])
            ->log('Профиль обновлен через API');

        return response()->json([
            'message' => 'Profile updated successfully',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'updated_at' => $employee->updated_at,
            ]
        ]);
    }

    /**
     * Получить список токенов пользователя
     */
    public function getTokens(Request $request)
    {
        $employee = $request->user();

        // Логируем запрос списка токенов через API
        activity()
            ->performedOn($employee)
            ->causedBy($employee)
            ->withProperties([
                'tokens_count' => $employee->tokens()->count(),
                'token_name' => $request->user()->currentAccessToken()->name,
                'via_api' => true
            ])
            ->log('Запрошен список токенов через API');

        return response()->json([
            'tokens' => $employee->tokens->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at,
                    'last_used_at' => $token->last_used_at,
                ];
            }),
            'total_tokens' => $employee->tokens()->count(),
        ]);
    }
}
