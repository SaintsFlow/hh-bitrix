<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\ClientIntegrationController;

// Главная страница - редирект на авторизацию
Route::get('/', function () {
    return redirect()->route('login');
});

// Авторизация
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Маршруты супер-админа
Route::middleware(['auth', 'role:super-admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Клиенты
    Route::get('/clients', [SuperAdminController::class, 'clients'])->name('clients');
    Route::get('/clients/create', [SuperAdminController::class, 'createClient'])->name('clients.create');
    Route::post('/clients', [SuperAdminController::class, 'storeClient'])->name('clients.store');
    Route::get('/clients/{client}/edit', [SuperAdminController::class, 'editClient'])->name('clients.edit');
    Route::put('/clients/{client}', [SuperAdminController::class, 'updateClient'])->name('clients.update');
    Route::patch('/clients/{client}/extend', [SuperAdminController::class, 'extendSubscription'])->name('clients.extend');
    Route::patch('/clients/{client}/toggle', [SuperAdminController::class, 'toggleClientStatus'])->name('clients.toggle');

    // Сотрудники
    Route::get('/employees', [SuperAdminController::class, 'employees'])->name('employees');
    Route::get('/employees/create', [SuperAdminController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [SuperAdminController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [SuperAdminController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [SuperAdminController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{employee}', [SuperAdminController::class, 'destroyEmployee'])->name('employees.destroy');
    Route::post('/employees/{employee}/token', [SuperAdminController::class, 'generateToken'])->name('employees.token');
    Route::delete('/employees/{employee}/token', [SuperAdminController::class, 'revokeToken'])->name('employees.revoke-token');

    // Логи активности
    Route::get('/activity-log', [SuperAdminController::class, 'activityLog'])->name('activity-log');

    // Интеграции
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [IntegrationController::class, 'index'])->name('index');
        Route::get('/create', [IntegrationController::class, 'create'])->name('create');
        Route::post('/', [IntegrationController::class, 'store'])->name('store');
        Route::get('/{integration}', [IntegrationController::class, 'show'])->name('show');
        Route::get('/{integration}/edit', [IntegrationController::class, 'edit'])->name('edit');
        Route::put('/{integration}', [IntegrationController::class, 'update'])->name('update');
        Route::delete('/{integration}', [IntegrationController::class, 'destroy'])->name('destroy');
        Route::post('/{integration}/test', [IntegrationController::class, 'testConnection'])->name('test');
        Route::post('/{integration}/toggle', [IntegrationController::class, 'toggleStatus'])->name('toggle');
    });
});

// Маршруты клиента
Route::middleware(['auth:client', 'subscription.active'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientController::class, 'dashboard'])->name('dashboard');

    // Сотрудники
    Route::get('/employees', [ClientController::class, 'employees'])->name('employees.index');
    Route::get('/employees/create', [ClientController::class, 'createEmployee'])->name('employees.create');
    Route::post('/employees', [ClientController::class, 'storeEmployee'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [ClientController::class, 'editEmployee'])->name('employees.edit');
    Route::put('/employees/{employee}', [ClientController::class, 'updateEmployee'])->name('employees.update');
    Route::delete('/employees/{employee}', [ClientController::class, 'destroyEmployee'])->name('employees.destroy');
    Route::post('/employees/{employee}/issue-token', [ClientController::class, 'issueToken'])->name('employees.issue-token');
    Route::delete('/employees/{employee}/revoke-token', [ClientController::class, 'revokeToken'])->name('employees.revoke-token');

    // Логи активности
    Route::get('/activity-log', [ClientController::class, 'activityLog'])->name('activity-log');

    // Уведомления
    Route::get('/notifications', [ClientController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{notification}/mark-read', [ClientController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [ClientController::class, 'markAllNotificationsAsRead'])->name('notifications.mark-all-read');

    // Интеграции
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [ClientIntegrationController::class, 'index'])->name('index');
        Route::get('/create', [ClientIntegrationController::class, 'create'])->name('create');
        Route::post('/', [ClientIntegrationController::class, 'store'])->name('store');
        Route::get('/{integration}', [ClientIntegrationController::class, 'show'])->name('show');
        Route::get('/{integration}/edit', [ClientIntegrationController::class, 'edit'])->name('edit');
        Route::put('/{integration}', [ClientIntegrationController::class, 'update'])->name('update');
        Route::delete('/{integration}', [ClientIntegrationController::class, 'destroy'])->name('destroy');
        Route::post('/{integration}/test', [ClientIntegrationController::class, 'testConnection'])->name('test');
        Route::post('/{integration}/toggle', [ClientIntegrationController::class, 'toggleStatus'])->name('toggle');
    });
});

// Маршруты сотрудника
Route::middleware(['auth:employee', 'subscription.active'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile/edit', [EmployeeController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [EmployeeController::class, 'updateProfile'])->name('profile.update');
});
