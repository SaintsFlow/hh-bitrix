@extends('layouts.app')

@section('title', 'Создать интеграцию')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Создать интеграцию</h1>
        <a href="{{ route('super-admin.integrations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Основные настройки</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('super-admin.integrations.store') }}" id="integrationForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Клиент *</label>
                                <select class="form-select" id="client_id" name="client_id" required>
                                    <option value="">Выберите клиента</option>
                                    @foreach($clients as $clientOption)
                                    <option value="{{ $clientOption->id }}" {{ ($client && $client->id == $clientOption->id) ? 'selected' : '' }}>
                                        {{ $clientOption->name }} ({{ $clientOption->email }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Тип интеграции *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Выберите тип</option>
                                    @foreach($integrationTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">Название интеграции *</label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    placeholder="Например: Основная CRM интеграция">
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Статус</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        Активна
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic settings based on integration type -->
                        <div id="integration-settings">
                            <!-- Will be populated by JavaScript -->
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('super-admin.integrations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Отменить
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Создать интеграцию
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Справка по интеграциям</h5>
                </div>
                <div class="card-body">
                    <div id="integration-help">
                        <p class="text-muted">Выберите тип интеграции, чтобы увидеть инструкции по настройке.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Templates for different integration types -->
<template id="crm-settings-template">
    <div class="mb-3">
        <h6>Настройки CRM</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="settings_api_url" class="form-label">API URL *</label>
                <input type="url" class="form-control" name="settings[api_url]"
                    placeholder="https://api.crm-system.com/v1" required>
                <div class="form-text">Базовый URL для API вашей CRM системы</div>
            </div>
            <div class="col-12 mb-3">
                <label for="settings_api_key" class="form-label">API ключ *</label>
                <input type="text" class="form-control" name="settings[api_key]"
                    placeholder="your-api-key-here" required>
                <div class="form-text">Ключ доступа к API</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="settings_funnel_id" class="form-label">ID воронки</label>
                <input type="text" class="form-control" name="settings[funnel_id]"
                    placeholder="123">
                <div class="form-text">ID воронки для новых лидов</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="settings_stage_id" class="form-label">ID этапа</label>
                <input type="text" class="form-control" name="settings[stage_id]"
                    placeholder="456">
                <div class="form-text">ID этапа для новых лидов</div>
            </div>
        </div>
    </div>
</template>

<template id="telegram-settings-template">
    <div class="mb-3">
        <h6>Настройки Telegram Bot</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="settings_bot_token" class="form-label">Токен бота *</label>
                <input type="text" class="form-control" name="settings[bot_token]"
                    placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz" required>
                <div class="form-text">Токен, полученный от @BotFather</div>
            </div>
            <div class="col-12 mb-3">
                <label for="settings_chat_id" class="form-label">Chat ID *</label>
                <input type="text" class="form-control" name="settings[chat_id]"
                    placeholder="-1001234567890" required>
                <div class="form-text">ID чата или канала для отправки уведомлений</div>
            </div>
        </div>
    </div>
</template>

<template id="webhook-settings-template">
    <div class="mb-3">
        <h6>Настройки Webhook</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="settings_webhook_url" class="form-label">Webhook URL *</label>
                <input type="url" class="form-control" name="settings[webhook_url]"
                    placeholder="https://your-service.com/webhook" required>
                <div class="form-text">URL для отправки POST запросов</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="settings_method" class="form-label">HTTP метод</label>
                <select class="form-select" name="settings[method]">
                    <option value="POST" selected>POST</option>
                    <option value="PUT">PUT</option>
                    <option value="PATCH">PATCH</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="settings_timeout" class="form-label">Таймаут (сек)</label>
                <input type="number" class="form-control" name="settings[timeout]"
                    value="30" min="5" max="300">
            </div>
            <div class="col-12 mb-3">
                <label for="settings_headers" class="form-label">Дополнительные заголовки</label>
                <textarea class="form-control" name="settings[headers]" rows="3"
                    placeholder='{"Authorization": "Bearer your-token", "X-Custom": "value"}'></textarea>
                <div class="form-text">JSON объект с заголовками</div>
            </div>
        </div>
    </div>
</template>

<template id="email-settings-template">
    <div class="mb-3">
        <h6>Настройки Email</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label for="settings_recipients" class="form-label">Получатели *</label>
                <textarea class="form-control" name="settings[recipients]" rows="3" required
                    placeholder="admin@company.com&#10;hr@company.com&#10;manager@company.com"></textarea>
                <div class="form-text">Один email адрес на строку</div>
            </div>
            <div class="col-12 mb-3">
                <label for="settings_subject_template" class="form-label">Шаблон темы</label>
                <input type="text" class="form-control" name="settings[subject_template]"
                    placeholder="Новое резюме: {candidate_name}"
                    value="Новое резюме: {candidate_name}">
                <div class="form-text">Доступные переменные: {candidate_name}, {position}, {company}</div>
            </div>
        </div>
    </div>
</template>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const settingsContainer = document.getElementById('integration-settings');
        const helpContainer = document.getElementById('integration-help');

        const helpTexts = {
            crm: '<h6>CRM интеграция</h6><p>Отправляет данные резюме в вашу CRM систему как новый лид.</p><ul><li>Настройте API доступ в вашей CRM</li><li>Получите API ключ</li><li>Укажите ID воронки и этапа для новых лидов</li></ul>',
            telegram: '<h6>Telegram Bot</h6><p>Отправляет уведомления о новых резюме в Telegram чат или канал.</p><ul><li>Создайте бота через @BotFather</li><li>Получите токен бота</li><li>Добавьте бота в чат/канал</li><li>Получите Chat ID</li></ul>',
            webhook: '<h6>Custom Webhook</h6><p>Отправляет данные резюме на ваш собственный endpoint.</p><ul><li>Подготовьте endpoint для приема POST запросов</li><li>Настройте аутентификацию через заголовки</li><li>Обрабатывайте JSON данные резюме</li></ul>',
            email: '<h6>Email уведомления</h6><p>Отправляет данные резюме по email с приложенным файлом.</p><ul><li>Укажите список получателей</li><li>Настройте шаблон темы письма</li><li>Резюме будет отправлено как вложение</li></ul>'
        };

        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;

            // Clear previous settings
            settingsContainer.innerHTML = '';

            if (selectedType) {
                // Get template and clone it
                const template = document.getElementById(selectedType + '-settings-template');
                if (template) {
                    const clone = template.content.cloneNode(true);
                    settingsContainer.appendChild(clone);
                }

                // Update help text
                helpContainer.innerHTML = helpTexts[selectedType] || '<p class="text-muted">Справка для этого типа интеграции недоступна.</p>';
            } else {
                helpContainer.innerHTML = '<p class="text-muted">Выберите тип интеграции, чтобы увидеть инструкции по настройке.</p>';
            }
        });

        // Auto-generate name based on type and client
        const clientSelect = document.getElementById('client_id');
        const nameInput = document.getElementById('name');

        function updateName() {
            const clientName = clientSelect.options[clientSelect.selectedIndex]?.text?.split(' (')[0] || '';
            const typeName = typeSelect.options[typeSelect.selectedIndex]?.text || '';

            if (clientName && typeName && !nameInput.value) {
                nameInput.value = `${typeName} - ${clientName}`;
            }
        }

        clientSelect.addEventListener('change', updateName);
        typeSelect.addEventListener('change', updateName);
    });
</script>
@endsection