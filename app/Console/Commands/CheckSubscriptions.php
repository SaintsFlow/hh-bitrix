<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\SubscriptionNotification;
use Carbon\Carbon;

class CheckSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка подписок клиентов и отправка уведомлений';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $warningDate = $now->copy()->addDays(7); // Предупреждение за 7 дней

        // Получаем всех активных клиентов
        $clients = Client::where('is_active', true)->get();

        $warningCount = 0;
        $expiredCount = 0;
        $tokensDisabled = 0;

        foreach ($clients as $client) {
            $subscriptionEnd = Carbon::parse($client->subscription_end_date);

            // Проверяем истекшие подписки
            if ($subscriptionEnd->isPast()) {
                // Отключаем токены всех сотрудников
                $employeeTokensCount = 0;
                foreach ($client->employees as $employee) {
                    $tokenCount = $employee->tokens()->count();
                    if ($tokenCount > 0) {
                        $employee->tokens()->delete();
                        $employeeTokensCount += $tokenCount;
                    }
                }
                $tokensDisabled += $employeeTokensCount;

                // Создаем уведомление об истечении подписки (если его еще нет)
                $existingNotification = SubscriptionNotification::where('client_id', $client->id)
                    ->where('type', 'expired')
                    ->whereDate('sent_at', $now->toDateString())
                    ->first();

                if (!$existingNotification) {
                    SubscriptionNotification::create([
                        'client_id' => $client->id,
                        'type' => 'expired',
                        'message' => "Ваша подписка истекла {$subscriptionEnd->format('d.m.Y')}. Все токены сотрудников отключены. Обратитесь к администратору для продления.",
                        'sent_at' => $now,
                    ]);
                    $expiredCount++;
                }
            }
            // Проверяем подписки, которые истекают в течение 7 дней
            elseif ($subscriptionEnd->lte($warningDate)) {
                $daysLeft = $now->diffInDays($subscriptionEnd);
                if ($daysLeft == 0) {
                    $daysText = 'сегодня';
                } elseif ($daysLeft == 1) {
                    $daysText = 'завтра';
                } else {
                    $daysText = "через {$daysLeft} дн.";
                }

                // Создаем предупреждающее уведомление (если его еще нет)
                $existingWarning = SubscriptionNotification::where('client_id', $client->id)
                    ->where('type', 'warning')
                    ->whereDate('sent_at', $now->toDateString())
                    ->first();

                if (!$existingWarning) {
                    SubscriptionNotification::create([
                        'client_id' => $client->id,
                        'type' => 'warning',
                        'message' => "Ваша подписка истекает {$daysText} ({$subscriptionEnd->format('d.m.Y')}). Обратитесь к администратору для продления.",
                        'sent_at' => $now,
                    ]);
                    $warningCount++;
                }
            }
        }

        $this->info("Обработано клиентов: {$clients->count()}");
        $this->info("Предупреждений отправлено: {$warningCount}");
        $this->info("Уведомлений об истечении: {$expiredCount}");
        $this->info("Токенов отключено: {$tokensDisabled}");

        return Command::SUCCESS;
    }
}
