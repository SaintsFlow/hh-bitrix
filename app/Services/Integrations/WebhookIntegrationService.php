<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;

class WebhookIntegrationService extends BaseIntegrationService
{
    protected string $serviceName = 'Webhook Integration';

    public function testConnection(): bool
    {
        try {
            $settings = $this->integration->settings;
            $webhookUrl = $settings['webhook_url'] ?? null;

            if (!$webhookUrl) {
                $this->logError('Missing webhook URL');
                return false;
            }

            if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
                $this->logError('Invalid webhook URL');
                return false;
            }

            $method = strtoupper($settings['method'] ?? 'POST');
            $headers = $settings['headers'] ?? [];

            $testData = [
                'test' => true,
                'message' => 'Webhook connection test',
                'timestamp' => now()->toISOString(),
                'integration_id' => $this->integration->id
            ];

            $response = Http::withHeaders(array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HH-Bitrix-Webhook/1.0'
            ], $headers))
                ->timeout(15)
                ->{strtolower($method)}($webhookUrl, $testData);

            if ($response->successful()) {
                $this->logInfo('Webhook connection test successful', [
                    'webhook_url' => $webhookUrl,
                    'response_code' => $response->status()
                ]);
                return true;
            } else {
                $this->logError('Webhook connection test failed', [
                    'webhook_url' => $webhookUrl,
                    'response_code' => $response->status()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logError('Webhook connection test exception', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendData(array $data): array
    {
        try {
            $settings = $this->integration->settings;
            $webhookUrl = $settings['webhook_url'] ?? null;

            if (!$webhookUrl) {
                return [
                    'success' => false,
                    'message' => 'Webhook URL not configured'
                ];
            }

            $method = strtoupper($settings['method'] ?? 'POST');
            $headers = $settings['headers'] ?? [];
            $timeout = $settings['timeout'] ?? 30;

            $formattedData = $this->formatResumeData($data);

            $response = Http::withHeaders(array_merge([
                'Content-Type' => 'application/json',
                'User-Agent' => 'HH-Bitrix-Webhook/1.0'
            ], $headers))
                ->timeout($timeout)
                ->{strtolower($method)}($webhookUrl, $formattedData);

            if ($response->successful()) {
                $this->logInfo('Data sent to webhook successfully', [
                    'webhook_url' => $webhookUrl,
                    'response_code' => $response->status()
                ]);

                return [
                    'success' => true,
                    'message' => 'Data sent successfully'
                ];
            } else {
                $this->logError('Failed to send data to webhook', [
                    'webhook_url' => $webhookUrl,
                    'response_code' => $response->status()
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send data'
                ];
            }
        } catch (\Exception $e) {
            $this->logError('Exception while sending data to webhook', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage()
            ];
        }
    }

    public function sendNotification(array $data): array
    {
        return $this->sendData($data);
    }

    public function getStatus(): array
    {
        $baseStatus = parent::getStatus();

        return array_merge($baseStatus, [
            'webhook_configured' => !empty($this->integration->settings['webhook_url'] ?? null),
            'service_name' => $this->serviceName
        ]);
    }

    public function getDefaultConfig(): array
    {
        return [
            'webhook_url' => '',
            'method' => 'POST',
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];
    }
}
