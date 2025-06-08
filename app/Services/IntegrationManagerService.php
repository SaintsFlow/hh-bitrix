<?php

namespace App\Services;

use App\Models\Client;
use App\Models\IntegrationSetting;
use App\Contracts\IntegrationServiceInterface;
use App\Services\Integrations\CrmIntegrationService;
use App\Services\Integrations\TelegramIntegrationService;
use App\Services\Integrations\WebhookIntegrationService;
use App\Services\Integrations\EmailIntegrationService;
use Illuminate\Support\Facades\Log;

class IntegrationManagerService
{
    private array $serviceMap = [
        'crm' => CrmIntegrationService::class,
        'telegram' => TelegramIntegrationService::class,
        'webhook' => WebhookIntegrationService::class,
        'email' => EmailIntegrationService::class,
    ];

    /**
     * Отправить данные резюме во все активные интеграции клиента
     */
    public function sendResumeData(Client $client, array $resumeData): array
    {
        $results = [];
        $integrations = $client->activeIntegrations()->get();

        Log::info('Sending resume data to integrations', [
            'client_id' => $client->id,
            'integrations_count' => $integrations->count(),
            'candidate_name' => $resumeData['candidate_name'] ?? 'Unknown'
        ]);

        foreach ($integrations as $integration) {
            try {
                $service = $this->createIntegrationService($integration);

                if (!$service) {
                    $results[] = [
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'integration_type' => $integration->type,
                        'success' => false,
                        'error' => 'Service not implemented',
                        'message' => 'Сервис для данного типа интеграции не реализован'
                    ];
                    continue;
                }

                // Тестируем соединение перед отправкой
                if (!$service->testConnection()) {
                    $results[] = [
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'integration_type' => $integration->type,
                        'success' => false,
                        'error' => 'Connection failed',
                        'message' => 'Не удалось подключиться к внешней системе'
                    ];
                    continue;
                }

                // Отправляем данные
                $result = $service->sendData($resumeData);

                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => $result['success'],
                    'message' => $result['message'] ?? null,
                    'external_id' => $result['external_id'] ?? null,
                    'response_data' => $result['response_data'] ?? null,
                    'error' => $result['error'] ?? null,
                ];

                // Обновляем время последнего использования
                $integration->update(['last_used_at' => now()]);

                Log::info('Integration result', [
                    'integration_id' => $integration->id,
                    'success' => $result['success'],
                    'external_id' => $result['external_id'] ?? null
                ]);
            } catch (\Exception $e) {
                Log::error('Integration failed', [
                    'integration_id' => $integration->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => false,
                    'error' => 'Exception',
                    'message' => 'Произошла ошибка при отправке данных: ' . $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Отправить уведомление во все интеграции типа notification
     */
    public function sendNotification(Client $client, array $notificationData): array
    {
        $results = [];
        $integrations = $client->activeIntegrations()
            ->whereIn('type', ['telegram', 'email'])
            ->get();

        foreach ($integrations as $integration) {
            try {
                $service = $this->createIntegrationService($integration);

                if (!$service) {
                    continue;
                }

                $result = $service->sendNotification($notificationData);

                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => $result['success'],
                    'message' => $result['message'] ?? null,
                ];

                $integration->update(['last_used_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Notification failed', [
                    'integration_id' => $integration->id,
                    'error' => $e->getMessage()
                ]);

                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Тестировать все интеграции клиента
     */
    public function testAllIntegrations(Client $client): array
    {
        $results = [];
        $integrations = $client->integrationSettings()->get();

        foreach ($integrations as $integration) {
            try {
                $service = $this->createIntegrationService($integration);

                if (!$service) {
                    $results[] = [
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'integration_type' => $integration->type,
                        'success' => false,
                        'message' => 'Сервис не реализован'
                    ];
                    continue;
                }

                $isConnected = $service->testConnection();

                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => $isConnected,
                    'message' => $isConnected ? 'Подключение успешно' : 'Ошибка подключения'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->type,
                    'success' => false,
                    'message' => 'Ошибка: ' . $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Тестировать соединение с конкретной интеграцией
     */
    public function testConnection(IntegrationSetting $integration): array
    {
        try {
            $service = $this->createIntegrationService($integration);

            if (!$service) {
                return [
                    'success' => false,
                    'error' => 'Service not implemented',
                    'message' => 'Сервис для данного типа интеграции не реализован'
                ];
            }

            $isConnected = $service->testConnection();

            return [
                'success' => $isConnected,
                'message' => $isConnected ? 'Подключение успешно' : 'Ошибка подключения'
            ];
        } catch (\Exception $e) {
            Log::error('Integration connection test failed', [
                'integration_id' => $integration->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Ошибка: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Создать сервис интеграции для конкретной настройки
     */
    private function createIntegrationService(IntegrationSetting $integration): ?IntegrationServiceInterface
    {
        $serviceClass = $this->serviceMap[$integration->type] ?? null;

        if (!$serviceClass || !class_exists($serviceClass)) {
            Log::warning('Integration service not found', [
                'integration_type' => $integration->type,
                'integration_id' => $integration->id
            ]);
            return null;
        }

        return new $serviceClass($integration);
    }

    /**
     * Получить список доступных типов интеграций
     */
    public function getAvailableIntegrationTypes(): array
    {
        return [
            'crm' => [
                'name' => 'CRM система',
                'description' => 'Интеграция с внешней CRM системой',
                'fields' => ['api_url', 'api_key', 'funnel_id', 'stage_id']
            ],
            'telegram' => [
                'name' => 'Telegram уведомления',
                'description' => 'Отправка уведомлений в Telegram',
                'fields' => ['bot_token', 'chat_id']
            ],
            'webhook' => [
                'name' => 'Webhook',
                'description' => 'HTTP вызов произвольного URL',
                'fields' => ['webhook_url', 'method', 'headers']
            ],
            'email' => [
                'name' => 'Email уведомления',
                'description' => 'Отправка email уведомлений',
                'fields' => ['recipients', 'subject_template']
            ]
        ];
    }

    /**
     * Проверить, поддерживается ли тип интеграции
     */
    public function isIntegrationTypeSupported(string $type): bool
    {
        return array_key_exists($type, $this->serviceMap);
    }
}
