<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;

class BitrixCrmIntegrationService extends BaseIntegrationService
{
    protected string $serviceName = 'Bitrix Integration';


    public function testConnection(): bool
    {
        try {
            $settings = $this->integration->settings;
            $apiUrl = $settings['api_url'] ?? null;

            if (!$apiUrl) {
                $this->logError('Missing API configuration', ['integration_id' => $this->integration->id]);
                return false;
            }

            // Тестовый запрос к API
            $response = $this->makeHttpRequest('GET', rtrim($apiUrl, '/') . '/user.current', [
                'headers' => [
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
            $funnelId = $settings['funnel_id'] ?? null;
            $stageId = $settings['stage_id'] ?? null;

            if (!$apiUrl) {
                return [
                    'success' => false,
                    'error' => 'Missing API configuration',
                    'message' => 'Не настроены API URL или API ключ'
                ];
            }

            // Формируем данные для CRM
            $contacts = $this->findOrCreateContact($data);

            if (!$contacts['success']) {
                return [
                    'success' => false,
                    'error' => $contacts['error'] ?? 'Unknown error',
                    'message' => $contacts['message'] ?? 'Ошибка при поиске или создании контакта',
                    'response_data' => $contacts['response_data'] ?? null
                ];
            }
            $data['contacts'] = $contacts['response_data'];

            $crmData = $this->formatDataForCrm($data, $funnelId, $stageId);
            // dd($crmData);

            // Отправляем данные
            $response = $this->makeHttpRequest('POST', rtrim($apiUrl, '/') . '/crm.deal.add', [
                'headers' => [
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

            $leadId = $response['response_data']['result'] ?? [];
            if (empty($leadId)) {
                return [
                    'success' => false,
                    'error' => 'Empty response from CRM',
                    'message' => 'CRM не вернула данные о созданном лиде'
                ];
            }
            // Отправляем файл резюме если есть
            if (isset($data['resume_file']) && $leadId) {
                $this->uploadResumeFile($leadId, $data['resume_file'], $apiUrl);
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
                'response_data' => $leadId
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
        // $crmData = [
        //     'name' => $data['candidate_name'],
        //     'phone' => $data['candidate_phone'],
        //     'email' => $data['candidate_email'],
        //     'position' => $data['position'],
        //     'source' => $data['source'] ?? 'API',
        //     'notes' => $data['notes'] ?? '',
        //     'created_at' => $data['submitted_at'],
        //     'responsible' => [
        //         'employee_name' => $data['submitted_by']['employee_name'],
        //         'employee_email' => $data['submitted_by']['employee_email'],
        //         'company_name' => $data['submitted_by']['client_name'],
        //     ]
        // ];

        $crmData =
            [
                'FIELDS' => [
                    'TITLE' => 'Резюме' . '|' . $data['position']  . ' | ' . $data['candidate_name'] . ' | ' . $data['candidate_phone'],
                    "CONTACT_IDS" => $data['contacts'],
                    "SOURCE_ID" => "WEB",
                ],
            ];

        // Добавляем воронку и этап если указаны
        if ($funnelId) {
            $crmData['FIELDS']['CATEGORY_ID'] = $funnelId;
        }

        if ($stageId) {
            $crmData['FIELDS']['STAGE_ID'] = $stageId;
        }

        if (isset($data['custom_fields'])) {
            array_merge($crmData['FIELDS'], $data['custom_fields']);
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

    private function findOrCreateContact(array $data): array
    {
        try {
            $settings = $this->integration->settings;
            $apiUrl = $settings['api_url'] ?? null;
            $nameParts = explode(' ', $data['candidate_name'] ?? 'Unknown');
            $phone = $data['candidate_phone'] ?? null;
            if (!$phone) {
                return [
                    'success' => false,
                    'error' => 'Missing phone number',
                    'message' => 'Не указан номер телефона кандидата'
                ];
            }
            $name = $nameParts[1] ?? 'Unknown';
            $lastName = $nameParts[0] ?? '';
            $secondName = $nameParts[2] ?? '';
            $phone = '+' . preg_replace('/[^\d]/', '', $phone); // Удаляем все кроме цифр

            if (!$apiUrl) {
                return [
                    'success' => false,
                    'error' => 'Missing API configuration',
                    'message' => 'Не настроены API URL или API ключ'
                ];
            }

            // Отправляем данные
            $response = $this->makeHttpRequest('GET', rtrim($apiUrl, '/') . '/crm.duplicate.findbycomm', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' =>
                [
                    "entity_type" => "CONTACT",
                    "type" => "PHONE",
                    "values" => [
                        $phone
                    ],
                ],
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

            $contacts = $response['response_data']['result']['CONTACT'] ?? [];

            if (empty($contacts)) {

                $this->logInfo('Контакта не было, создаем', [
                    'integration_id' => $this->integration->id,
                ]);

                $responseContact = $this->makeHttpRequest('GET', rtrim($apiUrl, '/') . '/crm.contact.add', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'FIELDS' =>                     [
                            "NAME" => $name,
                            "LAST_NAME" => $lastName,
                            "SECOND_NAME" => $secondName,
                            "PHONE" => [
                                [
                                    "VALUE" => $phone,
                                    "VALUE_TYPE" => "WORK",
                                ],
                            ],
                            "EMAIL" => [
                                [
                                    "VALUE" => $data['candidate_email'] ?? '',
                                    "VALUE_TYPE" => "WORK",
                                ],
                            ],
                        ],
                    ],
                    'timeout' => 30,
                ]);

                if (!$response['success']) {
                    return [
                        'success' => false,
                        'error' => 'HTTP request failed',
                        'message' => 'Ошибка при отправке данных в CRM: ' . ($response['error'] ?? 'Unknown error'),
                        'response_data' => $response['result'] ?? null
                    ];
                } else {
                    $contacts[] = $responseContact['response_data']['result'] ?? [];
                }
            }

            $this->logInfo('Получены контакты Битрикс', [
                'integration_id' => $this->integration->id,
                'contacts' => $contacts,
            ]);

            return [
                'success' => true,
                'message' => '',
                'response_data' => $contacts
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

    /**
     * Загружает файл резюме как вложение к лиду
     */
    private function uploadResumeFile(string $leadId, array $fileData, string $apiUrl): bool
    {
        try {
            $uploadData = [
                'fields' => [
                    'ENTITY_ID' => $leadId,
                    'ENTITY_TYPE' => 'deal',
                    'COMMENT' => 'Загружено резюме',
                    'FILES' => [
                        [
                            $fileData['name'],
                            $fileData['content']
                        ]
                    ],
                ]
            ];

            $response = $this->makeHttpRequest('POST', rtrim($apiUrl, '/') . '/crm.timeline.comment.add/', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $uploadData,
                'timeout' => 60, // Больше времени для загрузки файла
            ]);
            dd($response);

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
            'funnel_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'ID воронки',
                'description' => 'Идентификатор воронки (обязательно)',
                'placeholder' => 'funnel_123'
            ],
            'stage_id' => [
                'type' => 'string',
                'required' => false,
                'label' => 'ID этапа',
                'description' => 'Идентификатор стадии (обязательно)',
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
