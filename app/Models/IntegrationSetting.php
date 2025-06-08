<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'type',
        'name',
        'is_active',
        'settings',
        'webhook_config',
        'webhook_url',
        'webhook_method',
        'webhook_headers',
    ];

    protected $casts = [
        'settings' => 'array',
        'webhook_config' => 'array',
        'webhook_headers' => 'array',
        'is_active' => 'boolean',
    ];

    // Типы интеграций
    const TYPE_CRM = 'crm';
    const TYPE_TELEGRAM = 'telegram';
    const TYPE_WEBHOOK = 'webhook';
    const TYPE_EMAIL = 'email';

    // Отношения
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // Скопы
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Методы для работы с настройками
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }

    public function getWebhookHeader(string $key, $default = null)
    {
        return data_get($this->webhook_headers, $key, $default);
    }

    // Проверки
    public function isCrmIntegration(): bool
    {
        return $this->type === self::TYPE_CRM;
    }

    public function isTelegramIntegration(): bool
    {
        return $this->type === self::TYPE_TELEGRAM;
    }

    public function isWebhookIntegration(): bool
    {
        return $this->type === self::TYPE_WEBHOOK;
    }

    // Получение всех доступных типов
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_CRM => 'CRM система',
            self::TYPE_TELEGRAM => 'Telegram уведомления',
            self::TYPE_WEBHOOK => 'Webhook',
            self::TYPE_EMAIL => 'Email уведомления',
        ];
    }
}
