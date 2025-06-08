<?php

namespace App\Services\Integrations;

class TelegramIntegrationService extends BaseIntegrationService
{
    protected string $serviceName = 'Telegram Integration';

    public function testConnection(): bool
    {
        try {
            $settings = $this->integration->settings;
            $botToken = $settings['bot_token'] ?? null;
            $chatId = $settings['chat_id'] ?? null;

            if (!$botToken || !$chatId) {
                $this->logError('Missing Telegram configuration', ['integration_id' => $this->integration->id]);
                return false;
            }

            // Тестовое сообщение
            $response = $this->makeHttpRequest('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => '🔧 Тест подключения к Telegram боту',
                    'parse_mode' => 'HTML'
                ],
                'timeout' => 10,
            ]);

            if ($response['success']) {
                $this->logInfo('Telegram connection test successful', [
                    'integration_id' => $this->integration->id,
                    'chat_id' => $chatId
                ]);
                return true;
            } else {
                $this->logError('Telegram connection test failed', [
                    'integration_id' => $this->integration->id,
                    'error' => $response['error'] ?? 'Unknown error'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logError('Telegram connection test exception', [
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
            $botToken = $settings['bot_token'] ?? null;
            $chatId = $settings['chat_id'] ?? null;

            if (!$botToken || !$chatId) {
                return [
                    'success' => false,
                    'error' => 'Missing configuration',
                    'message' => 'Не настроены токен бота или ID чата'
                ];
            }

            // Формируем сообщение
            $message = $this->formatResumeMessage($data);

            // Отправляем сообщение
            $response = $this->makeHttpRequest('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true
                ],
                'timeout' => 15,
            ]);

            if (!$response['success']) {
                return [
                    'success' => false,
                    'error' => 'Send failed',
                    'message' => 'Ошибка отправки в Telegram: ' . ($response['error'] ?? 'Unknown error')
                ];
            }

            $messageId = $response['response_data']['result']['message_id'] ?? null;

            // Отправляем файл резюме если есть
            if (isset($data['resume_file']) && $messageId) {
                $this->sendResumeFile($data['resume_file'], $botToken, $chatId);
            }

            $this->logInfo('Resume sent to Telegram successfully', [
                'integration_id' => $this->integration->id,
                'message_id' => $messageId,
                'candidate_name' => $data['candidate_name'] ?? 'Unknown'
            ]);

            return [
                'success' => true,
                'message' => 'Резюме успешно отправлено в Telegram',
                'external_id' => $messageId,
                'response_data' => $response['response_data']
            ];
        } catch (\Exception $e) {
            $this->logError('Failed to send resume to Telegram', [
                'error' => $e->getMessage(),
                'integration_id' => $this->integration->id
            ]);

            return [
                'success' => false,
                'error' => 'Exception',
                'message' => 'Произошла ошибка при отправке в Telegram: ' . $e->getMessage()
            ];
        }
    }

    public function sendNotification(array $data): array
    {
        try {
            $settings = $this->integration->settings;
            $botToken = $settings['bot_token'] ?? null;
            $chatId = $settings['chat_id'] ?? null;

            if (!$botToken || !$chatId) {
                return [
                    'success' => false,
                    'message' => 'Не настроены параметры Telegram'
                ];
            }

            // Формируем сообщение уведомления
            $message = $this->formatNotificationMessage($data);

            $response = $this->makeHttpRequest('POST', "https://api.telegram.org/bot{$botToken}/sendMessage", [
                'json' => [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true
                ],
                'timeout' => 15,
            ]);

            return [
                'success' => $response['success'],
                'message' => $response['success']
                    ? 'Уведомление отправлено в Telegram'
                    : 'Ошибка отправки уведомления: ' . ($response['error'] ?? 'Unknown error')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при отправке уведомления: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Форматирует сообщение о резюме для Telegram
     */
    private function formatResumeMessage(array $data): string
    {
        $message = "📄 <b>Новое резюме</b>\n\n";

        $message .= "👤 <b>Кандидат:</b> {$data['candidate_name']}\n";
        $message .= "📞 <b>Телефон:</b> <code>{$data['candidate_phone']}</code>\n";
        $message .= "📧 <b>Email:</b> <code>{$data['candidate_email']}</code>\n";
        $message .= "💼 <b>Должность:</b> {$data['position']}\n";

        if (!empty($data['source'])) {
            $message .= "📍 <b>Источник:</b> {$data['source']}\n";
        }

        if (!empty($data['notes'])) {
            $notes = mb_strlen($data['notes']) > 200
                ? mb_substr($data['notes'], 0, 200) . '...'
                : $data['notes'];
            $message .= "\n💬 <b>Заметки:</b>\n<i>{$notes}</i>\n";
        }

        $message .= "\n🏢 <b>Отправлено от:</b>\n";
        $message .= "• Сотрудник: {$data['submitted_by']['employee_name']}\n";
        $message .= "• Компания: {$data['submitted_by']['client_name']}\n";

        $message .= "\n⏰ <b>Время:</b> " . date('d.m.Y H:i', strtotime($data['submitted_at']));

        // Добавляем теги если есть
        if (!empty($data['tags']) && is_array($data['tags'])) {
            $message .= "\n\n🏷️ <b>Теги:</b> " . implode(', ', array_map(function ($tag) {
                return "#{$tag}";
            }, $data['tags']));
        }

        // Добавляем приоритет если указан
        if (!empty($data['priority'])) {
            $priorityEmoji = [
                'low' => '🟢',
                'normal' => '🟡',
                'high' => '🟠',
                'urgent' => '🔴'
            ];
            $emoji = $priorityEmoji[$data['priority']] ?? '⚪';
            $message .= "\n{$emoji} <b>Приоритет:</b> " . ucfirst($data['priority']);
        }

        return $message;
    }

    /**
     * Форматирует уведомление для Telegram
     */
    private function formatNotificationMessage(array $data): string
    {
        $type = $data['type'] ?? 'notification';
        $title = $data['title'] ?? 'Уведомление';
        $content = $data['message'] ?? '';

        $icons = [
            'info' => 'ℹ️',
            'warning' => '⚠️',
            'error' => '❌',
            'success' => '✅',
            'notification' => '🔔'
        ];

        $icon = $icons[$type] ?? '🔔';

        $message = "{$icon} <b>{$title}</b>\n\n";
        $message .= $content;

        if (isset($data['timestamp'])) {
            $message .= "\n\n⏰ " . date('d.m.Y H:i', strtotime($data['timestamp']));
        }

        return $message;
    }

    /**
     * Отправляет файл резюме в Telegram
     */
    private function sendResumeFile(array $fileData, string $botToken, string $chatId): bool
    {
        try {
            // Telegram поддерживает отправку файлов до 50MB
            if ($fileData['size'] > 50 * 1024 * 1024) {
                $this->logWarning('File too large for Telegram', [
                    'file_size' => $fileData['size'],
                    'file_name' => $fileData['name']
                ]);
                return false;
            }

            // Создаем временный файл
            $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
            file_put_contents($tempFile, base64_decode($fileData['content']));

            // Отправляем файл как документ
            $response = $this->makeHttpRequest('POST', "https://api.telegram.org/bot{$botToken}/sendDocument", [
                'multipart' => [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId
                    ],
                    [
                        'name' => 'document',
                        'contents' => fopen($tempFile, 'r'),
                        'filename' => $fileData['name']
                    ],
                    [
                        'name' => 'caption',
                        'contents' => '📎 Файл резюме'
                    ]
                ],
                'timeout' => 60,
            ]);

            // Удаляем временный файл
            unlink($tempFile);

            if ($response['success']) {
                $this->logInfo('Resume file sent to Telegram', [
                    'integration_id' => $this->integration->id,
                    'file_name' => $fileData['name']
                ]);
                return true;
            } else {
                $this->logError('Failed to send file to Telegram', [
                    'integration_id' => $this->integration->id,
                    'error' => $response['error'] ?? 'Unknown error'
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logError('Exception during Telegram file upload', [
                'integration_id' => $this->integration->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getDefaultConfig(): array
    {
        return [
            'bot_token' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Bot Token',
                'description' => 'Токен бота, полученный от @BotFather',
                'placeholder' => '1234567890:ABCdefGHIjklMNOpqrSTUvwxyz'
            ],
            'chat_id' => [
                'type' => 'string',
                'required' => true,
                'label' => 'Chat ID',
                'description' => 'ID чата или канала для отправки сообщений',
                'placeholder' => '-1001234567890 или @channel_name'
            ],
            'notification_template' => [
                'type' => 'textarea',
                'required' => false,
                'label' => 'Шаблон уведомления',
                'description' => 'Шаблон сообщения (необязательно)',
                'default' => '📄 Новое резюме: {candidate_name} на позицию {position}'
            ],
            'send_files' => [
                'type' => 'boolean',
                'required' => false,
                'label' => 'Отправлять файлы резюме',
                'description' => 'Отправлять прикрепленные файлы резюме в Telegram',
                'default' => true
            ],
            'parse_mode' => [
                'type' => 'select',
                'required' => false,
                'label' => 'Режим форматирования',
                'description' => 'Режим форматирования сообщений',
                'options' => [
                    'HTML' => 'HTML',
                    'Markdown' => 'Markdown',
                    'MarkdownV2' => 'MarkdownV2',
                    '' => 'Без форматирования'
                ],
                'default' => 'HTML'
            ]
        ];
    }
}
