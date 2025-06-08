<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Controllers\Api\UserApiController;

// Базовый маршрут для получения пользователя с проверкой подписки
Route::get('/user', [UserApiController::class, 'getUser'])->middleware(['auth:sanctum', 'token.subscription']);

// Группа API маршрутов с проверкой токена и подписки
Route::middleware(['auth:sanctum', 'token.subscription'])->group(function () {
    // Маршруты для получения данных сотрудника
    Route::get('/profile', [UserApiController::class, 'getProfile']);

    // Маршрут для обновления профиля сотрудника
    Route::put('/profile', [UserApiController::class, 'updateProfile']);

    // Маршрут для получения информации о токенах
    Route::get('/tokens', [UserApiController::class, 'getTokens']);

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
