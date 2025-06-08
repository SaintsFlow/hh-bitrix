<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;

class CrmIntegrationService extends BaseIntegrationService
{
    protected string $serviceName = 'CRM Integration';

    public function testConnection(): bool
    {
        try {
            $settings = $this->integration->settings;
            $apiUrl = $settings['api_url'] ?? null;
            $apiKey = $settings['api_key'] ?? null;

            if (!$apiUrl || !$apiKey) {
                $this->logError('Missing API configuration', ['integration_id' => $this->integration->id]);
                return false;
            }

            // Тестовый запрос к API
            $response = $this->makeHttpRequest('GET', rtrim($apiUrl, '/') . '/ping', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 10,
            ]);

            return $response['success'];
        } catch (\Exception $e) {
            $this->logError('Connection test failed', [
                'error' => $e->getMessage(),
                'integration_id' => $this->integration->id
            ]);
            return false;
        }
    }

    public function sendData(array $data): array
    {
        try {
            $settings = $this->integration->settings;
            $apiUrl = $settings['api_url'] ?? null;
            $apiKey = $settings['api_key'] ?? null;
            $funnelId = $settings['funnel_id'] ?? null;
            $stageId = $settings['stage_id'] ?? null;

            if (!$apiUrl || !$apiKey) {
                return [
                    'success' => false,
                    'error' => 'Missing API configuration',
                    'message' => 'Не настроены API URL или API ключ'
                ];
            }

            // Формируем данные для CRM
            $crmData = $this->formatDataForCrm($data, $funnelId, $stageId);

            // Отправляем данные
            $response = $this->makeHttpRequest('POST', rtrim($apiUrl, '/') . '/leads', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $crmData,
                'timeout' => 30,
            ]);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => 'HTTP request failed',
                    'message' => 'Ошибка при отправке данных в CRM: ' . ($response['error'] ?? 'Unknown error'),
                    'response_data' => $response['response_data'] ?? null
                ];
            }

            $responseData = $response['response_data'];
            $leadId = $responseData['id'] ?? $responseData['lead_id'] ?? null;

            // Отправляем файл резюме если есть
            if (isset($data['resume_file']) && $leadId) {
                $this->uploadResumeFile($leadId, $data['resume_file'], $apiUrl, $apiKey);
            }

            $this->logInfo('Data sent successfully to CRM', [
                'integration_id' => $this->integration->id,
                'lead_id' => $leadId,
                'candidate_name' => $data['candidate_name'] ?? 'Unknown'
            ]);

            return [
                'success' => true,
                'message' => 'Данные успешно отправлены в CRM',
                'external_id' => $leadId,
                'response_data' => $responseData
            ];
        } catch (\Exception $e) {
            $this->logError('Failed to send data to CRM', [
                'error' => $e->getMessage(),
                'integration_id' => $this->integration->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Exception',
                'message' => 'Произошла ошибка при отправке в CRM: ' . $e->getMessage()
            ];
        }
    }

    public function sendNotification(array $data): array
    {
        // CRM обычно не используется для отправки уведомлений
        return [
            'success' => false,
            'message' => 'CRM интеграция не поддерживает отправку уведомлений'
        ];
    }

    /**
     * Форматирует данные для отправки в CRM
     */
    private function formatDataForCrm(array $data, ?string $funnelId, ?string $stageId): array
    {
        $crmData = [
            'name' => $data['candidate_name'],
            'phone' => $data['candidate_phone'],
            'email' => $data['candidate_email'],
            'position' => $data['position'],
            'source' => $data['source'] ?? 'API',
            'notes' => $data['notes'] ?? '',
            'created_at' => $data['submitted_at'],
            'responsible' => [
                'employee_name' => $data['submitted_by']['employee_name'],
                'employee_email' => $data['submitted_by']['employee_email'],
                'company_name' => $data['submitted_by']['client_name'],
            ]
        ];

        // Добавляем воронку и этап если указаны
        if ($funnelId) {
            $crmData['funnel_id'] = $funnelId;
        }

        if ($stageId) {
            $crmData['stage_id'] = $stageId;
        }

        // Добавляем дополнительные поля если есть
        if (isset($data['priority'])) {
            $crmData['priority'] = $data['priority'];
        }

        if (isset($data['tags'])) {
            $crmData['tags'] = $data['tags'];
        }

        if (isset($data['custom_fields'])) {
            $crmData['custom_fields'] = $data['custom_fields'];
        }

        // Добавляем специфичные для воронки поля если они настроены
        $settings = $this->integration->settings;
        if (isset($settings['custom_field_mapping'])) {
            foreach ($settings['custom_field_mapping'] as $sourceField => $crmField) {
                if (isset($data[$sourceField])) {
                    $crmData[$crmField] = $data[$sourceField];
                }
            }
        }

        return $crmData;
    }

    /**
     * Загружает файл резюме как вложение к лиду
     */
    private function uploadResumeFile(string $leadId, array $fileData, string $apiUrl, string $apiKey): bool
    {
        try {
            $uploadData = [
                'lead_id' => $leadId,
                'file_name' => $fileData['name'],
                'file_size' => $fileData['size'],
                'mime_type' => $fileData['mime_type'],
                'file_content' => $fileData['content'], // base64
                'description' => 'Resume file uploaded via API'
            ];

            $response = $this->makeHttpRequest('POST', rtrim($apiUrl, '/') . '/leads/' . $leadId . '/attachments', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $uploadData,
                'timeout' => 60, // Больше времени для загрузки файла
            ]);

            if ($response['success']) {
                $this->logInfo('Resume file uploaded successfully', [
                    'integration_id' => $this->integration->id,
                    'lead_id' => $leadId,
                    'file_name' => $fileData['name']
                ]);
                return true;
            } else {
                $this->logError('Failed to upload resume file', [
                    'integration_id' => $this->integration->id,
                    'lead_id' => $leadId,
                    'error' => $response['error'] ?? 'Unknown error'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logError('Exception during file upload', [
                'integration_id' => $this->integration->id,
                'lead_id' => $leadId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getDefaultConfig(): array
    {
        return [
            'api_url' => [
                'type' => 'url',
                'required' => true,
                'label' => 'API URL',
                'description' => 'URL адрес API вашей CRM системы',
                'placeholder' => 'https://your-crm.com/api/v1'
            ],
            'api_key' => [
                'type' => 'password',
                'required' => true,
                'label' => 'API ключ',
                'description' => 'Ключ доступа к API CRM системы',
                'placeholder' => 'your-api-key-here'
            ],
            'funnel_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'ID воронки',
                'description' => 'Идентификатор воронки для новых лидов (необязательно)',
                'placeholder' => 'funnel_123'
            ],
            'stage_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'ID этапа',
                'description' => 'Идентификатор этапа для новых лидов (необязательно)',
                'placeholder' => 'stage_456'
            ],
            'responsible_user_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'ID ответственного',
                'description' => 'ID пользователя, ответственного за новые лиды',
                'placeholder' => 'user_789'
            ],
            'custom_field_mapping' => [
                'type' => 'json',
                'required' => false,
                'label' => 'Соответствие полей',
                'description' => 'JSON объект для сопоставления полей резюме с полями CRM',
                'placeholder' => '{"source_field": "crm_field"}'
            ],
            'send_files' => [
                'type' => 'boolean',
                'required' => false,
                'label' => 'Отправлять файлы резюме',
                'description' => 'Загружать файлы резюме как вложения к лидам',
                'default' => true
            ],
            'auto_assign' => [
                'type' => 'boolean',
                'required' => false,
                'label' => 'Автоматическое назначение',
                'description' => 'Автоматически назначать ответственного',
                'default' => false
            ]
        ];
    }
}
