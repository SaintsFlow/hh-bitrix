<?php

namespace App\Services\Integrations;

use App\Contracts\IntegrationServiceInterface;
use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseIntegrationService implements IntegrationServiceInterface
{
    protected IntegrationSetting $integration;

    public function __construct(IntegrationSetting $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Базовая отправка HTTP запроса
     */
    protected function sendHttpRequest(array $data, ?string $url = null): bool
    {
        try {
            $url = $url ?? $this->integration->webhook_url;
            $method = $this->integration->webhook_method ?? 'POST';
            $headers = $this->integration->webhook_headers ?? [];

            if (!$url) {
                Log::error('Integration webhook URL not configured', [
                    'integration_id' => $this->integration->id,
                    'type' => $this->integration->type
                ]);
                return false;
            }

            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->{strtolower($method)}($url, $data);

            $success = $response->successful();

            Log::info('Integration request sent', [
                'integration_id' => $this->integration->id,
                'type' => $this->integration->type,
                'url' => $url,
                'method' => $method,
                'status' => $response->status(),
                'success' => $success,
                'response_size' => strlen($response->body())
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::error('Integration request failed', [
                'integration_id' => $this->integration->id,
                'type' => $this->integration->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Универсальный HTTP запрос для интеграций
     */
    protected function makeHttpRequest(string $method, string $url, array $options = []): array
    {
        try {
            $timeout = $options['timeout'] ?? 30;
            $headers = $options['headers'] ?? [];

            $httpClient = Http::withHeaders($headers)->timeout($timeout);

            if (isset($options['json'])) {
                $response = $httpClient->{strtolower($method)}($url, $options['json']);
            } elseif (isset($options['form_params'])) {
                $response = $httpClient->asForm()->{strtolower($method)}($url, $options['form_params']);
            } elseif (isset($options['multipart'])) {
                $response = $httpClient->asMultipart()->{strtolower($method)}($url, $options['multipart']);
            } else {
                $response = $httpClient->{strtolower($method)}($url, $options['body'] ?? []);
            }

            $responseData = null;
            try {
                $responseData = $response->json();
            } catch (\Exception $e) {
                $responseData = ['body' => $response->body()];
            }

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_data' => $responseData,
                'error' => $response->failed() ? 'HTTP ' . $response->status() : null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'status_code' => 0,
                'response_data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Проверить базовое подключение
     */
    public function testConnection(): bool
    {
        return $this->sendHttpRequest(['test' => true, 'timestamp' => now()->toISOString()]);
    }

    /**
     * Получить базовый статус
     */
    public function getStatus(): array
    {
        return [
            'active' => $this->integration->is_active,
            'type' => $this->integration->type,
            'name' => $this->integration->name,
            'webhook_url' => $this->integration->webhook_url,
            'last_updated' => $this->integration->updated_at,
        ];
    }

    /**
     * Получить настройку интеграции
     */
    protected function getSetting(string $key, $default = null)
    {
        return $this->integration->getSetting($key, $default);
    }

    /**
     * Установить настройку интеграции
     */
    protected function setSetting(string $key, $value): void
    {
        $this->integration->setSetting($key, $value);
        $this->integration->save();
    }

    /**
     * Форматировать данные резюме для отправки
     */
    protected function formatResumeData(array $resumeData): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'source' => 'hr_system',
            'client_id' => $this->integration->client_id,
            'client_name' => $this->integration->client->name,
            'resume' => $resumeData,
            'integration' => [
                'type' => $this->integration->type,
                'name' => $this->integration->name,
            ]
        ];
    }

    /**
     * Логирование ошибок
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge($context, [
            'integration_id' => $this->integration->id,
            'integration_type' => $this->integration->type,
        ]));
    }

    /**
     * Логирование информации
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge($context, [
            'integration_id' => $this->integration->id,
            'integration_type' => $this->integration->type,
        ]));
    }
}
