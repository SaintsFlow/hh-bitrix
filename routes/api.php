<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeApiController;

// Базовый маршрут для получения пользователя с проверкой подписки
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'token.subscription']);

// Группа API маршрутов с проверкой токена и подписки
Route::middleware(['auth:sanctum', 'token.subscription'])->group(function () {
    // Маршруты для получения данных сотрудника
    Route::get('/profile', function (Request $request) {
        $employee = $request->user();
        $client = $employee->client;

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
    });

    // Маршрут для обновления профиля сотрудника
    Route::put('/profile', function (Request $request) {
        $employee = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:employees,email,' . $employee->id,
        ]);

        $employee->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'updated_at' => $employee->updated_at,
            ]
        ]);
    });

    // Маршрут для получения информации о токенах
    Route::get('/tokens', function (Request $request) {
        $employee = $request->user();

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
    });

    // Новые маршруты для EmployeeApiController
    Route::prefix('employee')->group(function () {
        // Получение данных сотрудника и клиента
        Route::get('/data', [EmployeeApiController::class, 'getEmployeeData']);

        // Отправка резюме
        Route::post('/submit-resume', [EmployeeApiController::class, 'submitResume']);

        // Статус интеграций
        Route::get('/integration-status', [EmployeeApiController::class, 'getIntegrationStatus']);
    });
});
