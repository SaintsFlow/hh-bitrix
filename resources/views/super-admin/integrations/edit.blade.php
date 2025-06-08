@extends('layouts.app')

@section('title', 'Редактирование интеграции')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Редактирование интеграции</h1>
                    <p class="text-muted mb-0">Изменение настроек интеграции: {{ $integration->name }}</p>
                </div>
                <div>
                    <a href="{{ route('super-admin.integrations.show', $integration) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye"></i> Просмотр
                    </a>
                    <a href="{{ route('super-admin.integrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к списку
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('super-admin.integrations.update', $integration) }}">
                        @csrf
                        @method('PUT')

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle"></i> Основная информация
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Название интеграции</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $integration->name) }}" required>
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Тип интеграции</label>
                                            <select class="form-select @error('type') is-invalid @enderror"
                                                id="type" name="type" required onchange="updateSettingsFields()">
                                                <option value="">Выберите тип</option>
                                                <option value="bitrix" {{ old('type', $integration->type) == 'bitrix' ? 'selected' : '' }}>Bitrix24</option>
                                                <option value="crm" {{ old('type', $integration->type) == 'crm' ? 'selected' : '' }}>CRM</option>
                                                <option value="telegram" {{ old('type', $integration->type) == 'telegram' ? 'selected' : '' }}>Telegram</option>
                                                <option value="webhook" {{ old('type', $integration->type) == 'webhook' ? 'selected' : '' }}>Webhook</option>
                                                <option value="email" {{ old('type', $integration->type) == 'email' ? 'selected' : '' }}>Email</option>
                                            </select>
                                            @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="client_id" class="form-label">Клиент</label>
                                            <select class="form-select @error('client_id') is-invalid @enderror"
                                                id="client_id" name="client_id" required>
                                                <option value="">Выберите клиента</option>
                                                @foreach($clients as $client)
                                                <option value="{{ $client->id }}"
                                                    {{ old('client_id', $integration->client_id) == $client->id ? 'selected' : '' }}>
                                                    {{ $client->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('client_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="is_active" name="is_active" value="1"
                                                    {{ old('is_active', $integration->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Активная интеграция
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3">{{ old('description', $integration->description) }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Настройки интеграции -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-cogs"></i> Настройки интеграции
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="integration-settings">
                                    <!-- CRM Settings -->
                                    <div id="crm-settings" class="integration-type-settings" style="display: {{ old('type', $integration->type) == 'crm' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="crm_url" class="form-label">URL CRM</label>
                                                    <input type="url" class="form-control" id="crm_url" name="settings[crm_url]"
                                                        value="{{ old('settings.crm_url', $integration->settings['crm_url'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="api_key" class="form-label">API ключ</label>
                                                    <input type="text" class="form-control" id="api_key" name="settings[api_key]"
                                                        value="{{ old('settings.api_key', $integration->settings['api_key'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="funnel_id" class="form-label">ID воронки</label>
                                                    <input type="text" class="form-control" id="funnel_id" name="settings[funnel_id]"
                                                        value="{{ old('settings.funnel_id', $integration->settings['funnel_id'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="stage_id" class="form-label">ID этапа</label>
                                                    <input type="text" class="form-control" id="stage_id" name="settings[stage_id]"
                                                        value="{{ old('settings.stage_id', $integration->settings['stage_id'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Telegram Settings -->
                                    <div id="telegram-settings" class="integration-type-settings" style="display: {{ old('type', $integration->type) == 'telegram' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="bot_token" class="form-label">Токен бота</label>
                                                    <input type="text" class="form-control" id="bot_token" name="settings[bot_token]"
                                                        value="{{ old('settings.bot_token', $integration->settings['bot_token'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="chat_id" class="form-label">ID чата</label>
                                                    <input type="text" class="form-control" id="chat_id" name="settings[chat_id]"
                                                        value="{{ old('settings.chat_id', $integration->settings['chat_id'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Webhook Settings -->
                                    <div id="webhook-settings" class="integration-type-settings" style="display: {{ old('type', $integration->type) == 'webhook' ? 'block' : 'none' }}">
                                        <div class="mb-3">
                                            <label for="webhook_url" class="form-label">URL webhook</label>
                                            <input type="url" class="form-control" id="webhook_url" name="settings[webhook_url]"
                                                value="{{ old('settings.webhook_url', $integration->settings['webhook_url'] ?? '') }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="method" class="form-label">HTTP метод</label>
                                                    <select class="form-select" id="method" name="settings[method]">
                                                        <option value="POST" {{ old('settings.method', $integration->settings['method'] ?? 'POST') == 'POST' ? 'selected' : '' }}>POST</option>
                                                        <option value="PUT" {{ old('settings.method', $integration->settings['method'] ?? '') == 'PUT' ? 'selected' : '' }}>PUT</option>
                                                        <option value="PATCH" {{ old('settings.method', $integration->settings['method'] ?? '') == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="timeout" class="form-label">Timeout (сек)</label>
                                                    <input type="number" class="form-control" id="timeout" name="settings[timeout]"
                                                        min="1" max="60" value="{{ old('settings.timeout', $integration->settings['timeout'] ?? 30) }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Settings -->
                                    <div id="email-settings" class="integration-type-settings" style="display: {{ old('type', $integration->type) == 'email' ? 'block' : 'none' }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_host" class="form-label">SMTP хост</label>
                                                    <input type="text" class="form-control" id="smtp_host" name="settings[smtp_host]"
                                                        value="{{ old('settings.smtp_host', $integration->settings['smtp_host'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_port" class="form-label">SMTP порт</label>
                                                    <input type="number" class="form-control" id="smtp_port" name="settings[smtp_port]"
                                                        value="{{ old('settings.smtp_port', $integration->settings['smtp_port'] ?? 587) }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_username" class="form-label">SMTP пользователь</label>
                                                    <input type="text" class="form-control" id="smtp_username" name="settings[smtp_username]"
                                                        value="{{ old('settings.smtp_username', $integration->settings['smtp_username'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_password" class="form-label">SMTP пароль</label>
                                                    <input type="password" class="form-control" id="smtp_password" name="settings[smtp_password]"
                                                        value="{{ old('settings.smtp_password', $integration->settings['smtp_password'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="from_email" class="form-label">Email отправителя</label>
                                                    <input type="email" class="form-control" id="from_email" name="settings[from_email]"
                                                        value="{{ old('settings.from_email', $integration->settings['from_email'] ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="to_email" class="form-label">Email получателя</label>
                                                    <input type="email" class="form-control" id="to_email" name="settings[to_email]"
                                                        value="{{ old('settings.to_email', $integration->settings['to_email'] ?? '') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-info" onclick="testConnection()">
                                <i class="fas fa-plug"></i> Тестировать соединение
                            </button>
                            <div>
                                <a href="{{ route('super-admin.integrations.show', $integration) }}" class="btn btn-secondary me-2">
                                    Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Сохранить изменения
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Справка
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="help-content">
                                <div id="help-crm" class="help-section" style="display: {{ old('type', $integration->type) == 'crm' ? 'block' : 'none' }}">
                                    <h6>Настройка CRM интеграции</h6>
                                    <ul class="small">
                                        <li>URL CRM - полный адрес API вашей CRM</li>
                                        <li>API ключ - токен для авторизации в CRM</li>
                                        <li>ID воронки - идентификатор воронки продаж</li>
                                        <li>ID этапа - этап, на который добавлять лиды</li>
                                    </ul>
                                </div>

                                <div id="help-telegram" class="help-section" style="display: {{ old('type', $integration->type) == 'telegram' ? 'block' : 'none' }}">
                                    <h6>Настройка Telegram интеграции</h6>
                                    <ul class="small">
                                        <li>Токен бота - получите у @BotFather</li>
                                        <li>ID чата - можно получить у @userinfobot</li>
                                        <li>Бот должен быть добавлен в чат/канал</li>
                                    </ul>
                                </div>

                                <div id="help-webhook" class="help-section" style="display: {{ old('type', $integration->type) == 'webhook' ? 'block' : 'none' }}">
                                    <h6>Настройка Webhook интеграции</h6>
                                    <ul class="small">
                                        <li>URL webhook - адрес для отправки данных</li>
                                        <li>HTTP метод - обычно POST</li>
                                        <li>Timeout - максимальное время ожидания</li>
                                    </ul>
                                </div>

                                <div id="help-email" class="help-section" style="display: {{ old('type', $integration->type) == 'email' ? 'block' : 'none' }}">
                                    <h6>Настройка Email интеграции</h6>
                                    <ul class="small">
                                        <li>SMTP хост - сервер исходящей почты</li>
                                        <li>SMTP порт - обычно 587 или 465</li>
                                        <li>Данные для авторизации на SMTP</li>
                                        <li>Email отправителя и получателя</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateSettingsFields() {
        const type = document.getElementById('type').value;

        // Hide all settings sections
        document.querySelectorAll('.integration-type-settings').forEach(section => {
            section.style.display = 'none';
        });

        // Hide all help sections
        document.querySelectorAll('.help-section').forEach(section => {
            section.style.display = 'none';
        });

        // Show relevant sections
        if (type) {
            const settingsSection = document.getElementById(`${type}-settings`);
            const helpSection = document.getElementById(`help-${type}`);

            if (settingsSection) settingsSection.style.display = 'block';
            if (helpSection) helpSection.style.display = 'block';
        }
    }

    function testConnection() {
        const formData = new FormData(document.querySelector('form'));
        const data = {};

        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('settings[')) {
                const settingKey = key.match(/settings\[(.+)\]/)[1];
                if (!data.settings) data.settings = {};
                data.settings[settingKey] = value;
            } else {
                data[key] = value;
            }
        }

        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Тестирование...';
        button.disabled = true;

        fetch(`{{ route('super-admin.integrations.test', $integration) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                button.innerHTML = originalText;
                button.disabled = false;

                const alertClass = data.success ? 'alert-success' : 'alert-danger';
                const icon = data.success ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

                const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon}"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alert);

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    const alertElement = document.querySelector('.alert');
                    if (alertElement) {
                        const bsAlert = new bootstrap.Alert(alertElement);
                        bsAlert.close();
                    }
                }, 5000);
            })
            .catch(error => {
                button.innerHTML = originalText;
                button.disabled = false;
                console.error('Error:', error);

                const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> Произошла ошибка при тестировании соединения
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alert);
            });
    }

    // Initialize form on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSettingsFields();
    });
</script>
@endpush