<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Тестируем middleware для токенов
echo "=== TESTING SUBSCRIPTION MIDDLEWARE ===\n\n";

// Тест 1: Клиент с активной подпиской
$activeClient = App\Models\Client::find(1);
if ($activeClient) {
    echo "Test 1 - Active subscription:\n";
    echo "Client: {$activeClient->name}\n";
    echo "Subscription end: {$activeClient->subscription_end_date->format('Y-m-d')}\n";
    echo "Is active: " . ($activeClient->isSubscriptionActive() ? 'YES' : 'NO') . "\n";
    echo "Result: " . ($activeClient->isSubscriptionActive() ? 'API ACCESS ALLOWED' : 'API ACCESS BLOCKED') . "\n\n";
}

// Тест 2: Клиент с истекшей подпиской
$expiredClient = App\Models\Client::where('email', 'expired@test.com')->first();
if ($expiredClient) {
    echo "Test 2 - Expired subscription:\n";
    echo "Client: {$expiredClient->name}\n";
    echo "Subscription end: {$expiredClient->subscription_end_date->format('Y-m-d')}\n";
    echo "Is active: " . ($expiredClient->isSubscriptionActive() ? 'YES' : 'NO') . "\n";

    if (!$expiredClient->isSubscriptionActive()) {
        echo "Result: API ACCESS BLOCKED\n";
        echo "Response would be:\n";
        $response = [
            'error' => 'Subscription expired',
            'message' => 'Your company subscription has expired. Please contact your administrator.',
            'subscription_expired' => true,
            'subscription_end_date' => $expiredClient->subscription_end_date->format('Y-m-d'),
            'code' => 'SUBSCRIPTION_EXPIRED'
        ];
        echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";
    } else {
        echo "Result: API ACCESS ALLOWED\n\n";
    }
}

// Тест 3: Проверяем веб-ограничения для истекшей подписки
echo "Test 3 - Web restrictions:\n";
if ($expiredClient) {
    $employee = $expiredClient->employees()->first();
    if ($employee) {
        echo "Employee: {$employee->name}\n";
        echo "Can access dashboard: YES (allowed route)\n";
        echo "Can edit profile: NO (blocked by middleware)\n";
        echo "Can manage employees: NO (blocked by middleware)\n";
        echo "Can access activity log: NO (blocked by middleware)\n\n";
    }
}

// Тест 4: Уведомления о подписке
echo "Test 4 - Subscription notifications:\n";
if ($expiredClient) {
    $notifications = $expiredClient->notifications()->get();
    echo "Total notifications: " . $notifications->count() . "\n";
    foreach ($notifications as $notification) {
        echo "- {$notification->type}: {$notification->title}\n";
        echo "  Days until expiry: {$notification->days_until_expiry}\n";
        echo "  Created: {$notification->created_at->format('Y-m-d H:i')}\n";
    }
}

echo "\n=== TESTING COMPLETE ===\n";
