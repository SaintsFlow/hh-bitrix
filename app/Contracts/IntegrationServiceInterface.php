<?php

namespace App\Contracts;

interface IntegrationServiceInterface
{
    /**
     * Отправить данные в интеграцию
     */
    public function sendData(array $data): array;

    /**
     * Проверить подключение к интеграции
     */
    public function testConnection(): bool;

    /**
     * Получить статус интеграции
     */
    public function getStatus(): array;

    /**
     * Получить конфигурацию по умолчанию
     */
    public function getDefaultConfig(): array;

    /**
     * Отправить уведомление через интеграцию
     */
    public function sendNotification(array $data): array;
}
