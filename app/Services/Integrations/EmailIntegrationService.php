<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Mail;

class EmailIntegrationService extends BaseIntegrationService
{
    protected string $serviceName = 'Email Integration';

    public function getDefaultConfig(): array
    {
        return [
            'recipients' => [
                'type' => 'array',
                'label' => 'Email получатели',
                'description' => 'Список email адресов для отправки резюме',
                'required' => true,
                'validation' => 'required|array|min:1',
                'items' => [
                    'type' => 'email',
                    'validation' => 'email'
                ],
                'default' => []
            ],
            'subject_template' => [
                'type' => 'text',
                'label' => 'Шаблон темы письма',
                'description' => 'Шаблон темы письма с плейсхолдерами: {candidate_name}, {position}, {client_name}, {date}, {time}',
                'required' => false,
                'validation' => 'string|max:255',
                'default' => 'Новое резюме: {candidate_name} - {position}'
            ],
            'send_attachments' => [
                'type' => 'boolean',
                'label' => 'Отправлять файлы резюме',
                'description' => 'Прикреплять файлы резюме к email письму',
                'required' => false,
                'default' => true
            ],
            'notification_enabled' => [
                'type' => 'boolean',
                'label' => 'Включить уведомления',
                'description' => 'Отправлять уведомления о системных событиях',
                'required' => false,
                'default' => true
            ],
            'priority_mapping' => [
                'type' => 'object',
                'label' => 'Настройка приоритетов',
                'description' => 'Настройка отображения приоритетов в письмах',
                'required' => false,
                'properties' => [
                    'show_priority' => [
                        'type' => 'boolean',
                        'label' => 'Показывать приоритет',
                        'default' => true
                    ],
                    'highlight_urgent' => [
                        'type' => 'boolean',
                        'label' => 'Выделять срочные',
                        'default' => true
                    ]
                ],
                'default' => [
                    'show_priority' => true,
                    'highlight_urgent' => true
                ]
            ]
        ];
    }

    public function testConnection(): bool
    {
        try {
            $settings = $this->integration->settings;
            $recipients = $settings['recipients'] ?? [];

            if (empty($recipients)) {
                $this->logError('No email recipients configured', ['integration_id' => $this->integration->id]);
                return false;
            }

            // Валидируем email адреса
            foreach ($recipients as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->logError('Invalid email address', [
                        'integration_id' => $this->integration->id,
                        'email' => $email
                    ]);
                    return false;
                }
            }

            // Отправляем тестовое письмо
            $this->sendTestEmail($recipients);

            $this->logInfo('Email connection test successful', [
                'integration_id' => $this->integration->id,
                'recipients_count' => count($recipients)
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logError('Email connection test failed', [
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
            $recipients = $settings['recipients'] ?? [];

            if (empty($recipients)) {
                return [
                    'success' => false,
                    'error' => 'No recipients',
                    'message' => 'Не настроены получатели email'
                ];
            }

            // Формируем данные письма
            $emailData = $this->prepareEmailData($data, $settings);

            // Отправляем письмо
            $this->sendResumeEmail($recipients, $emailData);

            $this->logInfo('Resume sent via email successfully', [
                'integration_id' => $this->integration->id,
                'recipients_count' => count($recipients),
                'candidate_name' => $data['candidate_name'] ?? 'Unknown'
            ]);

            return [
                'success' => true,
                'message' => 'Резюме успешно отправлено по email',
                'external_id' => uniqid('email_'),
                'response_data' => [
                    'recipients' => $recipients,
                    'subject' => $emailData['subject']
                ]
            ];
        } catch (\Exception $e) {
            $this->logError('Failed to send resume via email', [
                'error' => $e->getMessage(),
                'integration_id' => $this->integration->id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Exception',
                'message' => 'Произошла ошибка при отправке email: ' . $e->getMessage()
            ];
        }
    }

    public function sendNotification(array $data): array
    {
        try {
            $settings = $this->integration->settings;
            $recipients = $settings['recipients'] ?? [];

            if (empty($recipients)) {
                return [
                    'success' => false,
                    'message' => 'Не настроены получатели email'
                ];
            }

            // Отправляем уведомление
            $this->sendNotificationEmail($recipients, $data, $settings);

            return [
                'success' => true,
                'message' => 'Уведомление отправлено по email'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка при отправке уведомления: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Подготавливает данные для email
     */
    private function prepareEmailData(array $data, array $settings): array
    {
        $subjectTemplate = $settings['subject_template'] ?? 'Новое резюме: {candidate_name} - {position}';

        // Заменяем плейсхолдеры в теме
        $subject = $this->replacePlaceholders($subjectTemplate, $data);

        return [
            'subject' => $subject,
            'candidate' => [
                'name' => $data['candidate_name'],
                'phone' => $data['candidate_phone'],
                'email' => $data['candidate_email'],
                'position' => $data['position'],
                'source' => $data['source'] ?? 'API',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? 'normal',
                'tags' => $data['tags'] ?? [],
            ],
            'submitted_by' => $data['submitted_by'],
            'submitted_at' => $data['submitted_at'],
            'resume_file' => $data['resume_file'] ?? null,
            'integration_name' => $this->integration->name,
            'custom_fields' => $data['custom_fields'] ?? [],
        ];
    }

    /**
     * Отправляет тестовое письмо
     */
    private function sendTestEmail(array $recipients): void
    {
        $subject = 'Тест email интеграции - ' . $this->integration->name;

        $content = "
        <h2>🔧 Тест подключения Email интеграции</h2>
        
        <p>Это тестовое письмо для проверки настроек email интеграции.</p>
        
        <ul>
            <li><strong>Интеграция:</strong> {$this->integration->name}</li>
            <li><strong>Время отправки:</strong> " . now()->format('d.m.Y H:i:s') . "</li>
            <li><strong>Статус:</strong> ✅ Подключение работает</li>
        </ul>
        
        <p><small>Это сообщение отправлено автоматически системой HH-Bitrix.</small></p>
        ";

        foreach ($recipients as $recipient) {
            Mail::html($content, function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }
    }

    /**
     * Отправляет email с резюме
     */
    private function sendResumeEmail(array $recipients, array $emailData): void
    {
        $content = $this->buildResumeEmailContent($emailData);

        foreach ($recipients as $recipient) {
            Mail::html($content, function ($message) use ($recipient, $emailData) {
                $message->to($recipient)
                    ->subject($emailData['subject'])
                    ->from(config('mail.from.address'), config('mail.from.name'));

                // Прикрепляем файл резюме если есть
                if ($emailData['resume_file']) {
                    $fileData = $emailData['resume_file'];
                    $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
                    file_put_contents($tempFile, base64_decode($fileData['content']));

                    $message->attach($tempFile, [
                        'as' => $fileData['name'],
                        'mime' => $fileData['mime_type']
                    ]);

                    // Удалим временный файл после отправки
                    register_shutdown_function(function () use ($tempFile) {
                        if (file_exists($tempFile)) {
                            unlink($tempFile);
                        }
                    });
                }
            });
        }
    }

    /**
     * Отправляет уведомление по email
     */
    private function sendNotificationEmail(array $recipients, array $data, array $settings): void
    {
        $subject = $data['title'] ?? 'Уведомление от HH-Bitrix';
        $content = $this->buildNotificationEmailContent($data);

        foreach ($recipients as $recipient) {
            Mail::html($content, function ($message) use ($recipient, $subject) {
                $message->to($recipient)
                    ->subject($subject)
                    ->from(config('mail.from.address'), config('mail.from.name'));
            });
        }
    }

    /**
     * Создает HTML содержимое письма с резюме
     */
    private function buildResumeEmailContent(array $emailData): string
    {
        $candidate = $emailData['candidate'];
        $submittedBy = $emailData['submitted_by'];

        $priorityColors = [
            'low' => '#28a745',
            'normal' => '#6c757d',
            'high' => '#fd7e14',
            'urgent' => '#dc3545'
        ];

        $priorityColor = $priorityColors[$candidate['priority']] ?? '#6c757d';

        $content = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>📄 Новое резюме</h1>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px;'>
                <div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    <h2 style='color: #333; margin-top: 0;'>👤 Информация о кандидате</h2>
                    
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold; width: 30%;'>Имя:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$candidate['name']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Телефон:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><a href='tel:{$candidate['phone']}'>{$candidate['phone']}</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Email:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'><a href='mailto:{$candidate['email']}'>{$candidate['email']}</a></td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Должность:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$candidate['position']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Источник:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>{$candidate['source']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee; font-weight: bold;'>Приоритет:</td>
                            <td style='padding: 8px 0; border-bottom: 1px solid #eee;'>
                                <span style='background: {$priorityColor}; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px;'>
                                    " . strtoupper($candidate['priority']) . "
                                </span>
                            </td>
                        </tr>
                    </table>";

        // Добавляем заметки если есть
        if (!empty($candidate['notes'])) {
            $content .= "
                    <h3 style='color: #333; margin-top: 20px;'>💬 Заметки</h3>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 4px; border-left: 4px solid #007bff;'>
                        " . nl2br(htmlspecialchars($candidate['notes'])) . "
                    </div>";
        }

        // Добавляем теги если есть
        if (!empty($candidate['tags'])) {
            $tags = array_map(function ($tag) {
                return "<span style='background: #e9ecef; padding: 2px 6px; border-radius: 12px; font-size: 12px; margin-right: 4px;'>#{$tag}</span>";
            }, $candidate['tags']);

            $content .= "
                    <h3 style='color: #333; margin-top: 20px;'>🏷️ Теги</h3>
                    <div>" . implode('', $tags) . "</div>";
        }

        // Добавляем информацию о файле
        if ($emailData['resume_file']) {
            $fileData = $emailData['resume_file'];
            $fileSizeKb = round($fileData['size'] / 1024, 1);

            $content .= "
                    <h3 style='color: #333; margin-top: 20px;'>📎 Прикрепленный файл</h3>
                    <div style='background: #e3f2fd; padding: 10px; border-radius: 4px; border: 1px solid #2196f3;'>
                        <strong>{$fileData['name']}</strong><br>
                        <small>Размер: {$fileSizeKb} KB | Тип: {$fileData['mime_type']}</small>
                    </div>";
        }

        $content .= "
                </div>
                
                <div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 20px;'>
                    <h3 style='color: #333; margin-top: 0;'>🏢 Отправлено от</h3>
                    <p>
                        <strong>Сотрудник:</strong> {$submittedBy['employee_name']} ({$submittedBy['employee_email']})<br>
                        <strong>Компания:</strong> {$submittedBy['client_name']}<br>
                        <strong>Время:</strong> " . date('d.m.Y H:i', strtotime($emailData['submitted_at'])) . "
                    </p>
                </div>
                
                <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                    <p>Это письмо отправлено автоматически через интеграцию <strong>{$emailData['integration_name']}</strong></p>
                    <p>HH-Bitrix Resume Management System</p>
                </div>
            </div>
        </div>";

        return $content;
    }

    /**
     * Создает HTML содержимое уведомления
     */
    private function buildNotificationEmailContent(array $data): string
    {
        $type = $data['type'] ?? 'info';
        $title = $data['title'] ?? 'Уведомление';
        $message = $data['message'] ?? '';

        $typeColors = [
            'info' => '#17a2b8',
            'success' => '#28a745',
            'warning' => '#ffc107',
            'error' => '#dc3545'
        ];

        $typeIcons = [
            'info' => 'ℹ️',
            'success' => '✅',
            'warning' => '⚠️',
            'error' => '❌'
        ];

        $color = $typeColors[$type] ?? '#17a2b8';
        $icon = $typeIcons[$type] ?? 'ℹ️';

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: {$color}; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>{$icon} {$title}</h1>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px;'>
                <div style='background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    " . nl2br(htmlspecialchars($message)) . "
                    
                    " . (isset($data['timestamp']) ? "<p style='margin-top: 20px; color: #666; font-size: 14px;'><strong>Время:</strong> " . date('d.m.Y H:i', strtotime($data['timestamp'])) . "</p>" : "") . "
                </div>
                
                <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                    <p>HH-Bitrix Notification System</p>
                </div>
            </div>
        </div>";
    }

    /**
     * Заменяет плейсхолдеры в тексте
     */
    private function replacePlaceholders(string $template, array $data): string
    {
        $placeholders = [
            '{candidate_name}' => $data['candidate_name'] ?? '',
            '{candidate_email}' => $data['candidate_email'] ?? '',
            '{candidate_phone}' => $data['candidate_phone'] ?? '',
            '{position}' => $data['position'] ?? '',
            '{source}' => $data['source'] ?? 'API',
            '{employee_name}' => $data['submitted_by']['employee_name'] ?? '',
            '{client_name}' => $data['submitted_by']['client_name'] ?? '',
            '{date}' => date('d.m.Y', strtotime($data['submitted_at'] ?? now())),
            '{time}' => date('H:i', strtotime($data['submitted_at'] ?? now())),
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $template);
    }
}
