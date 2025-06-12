@extends('layouts.app')

@section('title', 'Редактирование интеграции')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Редактирование интеграции</h1>
                    <p class="text-muted mb-0">Изменение настроек: {{ $integration->name }}</p>
                </div>
                <div>
                    <a href="{{ route('client.integrations.show', $integration) }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-eye"></i> Просмотр
                    </a>
                    <a href="{{ route('client.integrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к списку
                    </a>
                </div>
            </div>

            @if(!auth()->user()->isSubscriptionActive())
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Внимание!</strong> Ваша подписка неактивна. Эта интеграция не будет работать.
            </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('client.integrations.update', $integration) }}">
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
                                            <label for="name" class="form-label">Название интеграции <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name', $integration->name) }}" required
                                                placeholder="Например: Отправка в Bitrix24">
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Тип интеграции</label>
                                            <input type="text" class="form-control" value="{{ ucfirst($integration->type) }}" readonly>
                                            <div class="form-text">Тип интеграции нельзя изменить после создания</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Описание</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                id="description" name="description" rows="3"
                                                placeholder="Краткое описание назначения этой интеграции">{{ old('description', $integration->description) }}</textarea>
                                            @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Статус интеграции</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    id="is_active" name="is_active" value="1"
                                                    {{ old('is_active', $integration->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Активная интеграция
                                                </label>
                                            </div>
                                            <div class="form-text">
                                                @if(auth()->user()->client->isSubscriptionActive())
                                                Включайте/выключайте интеграцию по необходимости
                                                @else
                                                Интеграция не будет работать без активной подписки
                                                @endif
                                            </div>
                                        </div>
                                    </div>
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
                                @switch($integration->type)
                                @case('crm')
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-info-circle"></i>
                                    Для настройки CRM интеграции вам потребуется API ключ и данные воронки от вашей CRM системы.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="crm_url" class="form-label">URL CRM <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control @error('settings.crm_url') is-invalid @enderror"
                                                id="crm_url" name="settings[crm_url]"
                                                value="{{ old('settings.crm_url', $integration->settings['crm_url'] ?? '') }}"
                                                placeholder="https://your-domain.bitrix24.ru" required>
                                            @error('settings.crm_url')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Полный URL вашей CRM системы</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="api_key" class="form-label">API ключ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('settings.api_key') is-invalid @enderror"
                                                id="api_key" name="settings[api_key]"
                                                value="{{ old('settings.api_key', $integration->settings['api_key'] ?? '') }}"
                                                placeholder="Введите API ключ" required>
                                            @error('settings.api_key')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="funnel_id" class="form-label">ID воронки</label>
                                            <input type="text" class="form-control" id="funnel_id" name="settings[funnel_id]"
                                                value="{{ old('settings.funnel_id', $integration->settings['funnel_id'] ?? '') }}"
                                                placeholder="Например: 1">
                                            <div class="form-text">Оставьте пустым для воронки по умолчанию</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stage_id" class="form-label">ID этапа</label>
                                            <input type="text" class="form-control" id="stage_id" name="settings[stage_id]"
                                                value="{{ old('settings.stage_id', $integration->settings['stage_id'] ?? '') }}"
                                                placeholder="Например: NEW">
                                            <div class="form-text">Этап, на который добавлять новые лиды</div>
                                        </div>
                                    </div>
                                </div>
                                @break

                                @case('telegram')
                                <div class="alert alert-info mb-4">
                                    <i class="fab fa-telegram"></i>
                                    Для настройки Telegram уведомлений создайте бота через @BotFather и получите токен.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bot_token" class="form-label">Токен бота <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('settings.bot_token') is-invalid @enderror"
                                                id="bot_token" name="settings[bot_token]"
                                                value="{{ old('settings.bot_token', $integration->settings['bot_token'] ?? '') }}"
                                                placeholder="123456789:ABCDefGhIJKLmnoPQRSTUVWXYZ" required>
                                            @error('settings.bot_token')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Получите у @BotFather в Telegram</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="chat_id" class="form-label">ID чата <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('settings.chat_id') is-invalid @enderror"
                                                id="chat_id" name="settings[chat_id]"
                                                value="{{ old('settings.chat_id', $integration->settings['chat_id'] ?? '') }}"
                                                placeholder="-123456789 или @channel_name" required>
                                            @error('settings.chat_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">ID чата или канала для отправки уведомлений</div>
                                        </div>
                                    </div>
                                </div>
                                @break

                                @case('webhook')
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-link"></i>
                                    Webhook позволяет отправлять данные резюме на ваш собственный API endpoint.
                                </div>

                                <div class="mb-3">
                                    <label for="webhook_url" class="form-label">URL webhook <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control @error('settings.webhook_url') is-invalid @enderror"
                                        id="webhook_url" name="settings[webhook_url]"
                                        value="{{ old('settings.webhook_url', $integration->settings['webhook_url'] ?? '') }}"
                                        placeholder="https://your-domain.com/api/webhook" required>
                                    @error('settings.webhook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">URL для получения POST запросов с данными резюме</div>
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
                                            <div class="form-text">Максимальное время ожидания ответа</div>
                                        </div>
                                    </div>
                                </div>
                                @break

                                @case('email')
                                <div class="alert alert-info mb-4">
                                    <i class="fas fa-envelope"></i>
                                    Настройте отправку уведомлений о новых резюме на email.
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_host" class="form-label">SMTP хост <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('settings.smtp_host') is-invalid @enderror"
                                                id="smtp_host" name="settings[smtp_host]"
                                                value="{{ old('settings.smtp_host', $integration->settings['smtp_host'] ?? '') }}"
                                                placeholder="smtp.gmail.com" required>
                                            @error('settings.smtp_host')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_port" class="form-label">SMTP порт <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('settings.smtp_port') is-invalid @enderror"
                                                id="smtp_port" name="settings[smtp_port]"
                                                value="{{ old('settings.smtp_port', $integration->settings['smtp_port'] ?? 587) }}"
                                                placeholder="587" required>
                                            @error('settings.smtp_port')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_username" class="form-label">SMTP пользователь <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('settings.smtp_username') is-invalid @enderror"
                                                id="smtp_username" name="settings[smtp_username]"
                                                value="{{ old('settings.smtp_username', $integration->settings['smtp_username'] ?? '') }}"
                                                placeholder="your-email@gmail.com" required>
                                            @error('settings.smtp_username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="smtp_password" class="form-label">SMTP пароль <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control @error('settings.smtp_password') is-invalid @enderror"
                                                id="smtp_password" name="settings[smtp_password]"
                                                value="{{ old('settings.smtp_password', $integration->settings['smtp_password'] ?? '') }}"
                                                placeholder="Пароль или app password" required>
                                            @error('settings.smtp_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="from_email" class="form-label">Email отправителя <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('settings.from_email') is-invalid @enderror"
                                                id="from_email" name="settings[from_email]"
                                                value="{{ old('settings.from_email', $integration->settings['from_email'] ?? '') }}"
                                                placeholder="noreply@your-domain.com" required>
                                            @error('settings.from_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="to_email" class="form-label">Email получателя <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('settings.to_email') is-invalid @enderror"
                                                id="to_email" name="settings[to_email]"
                                                value="{{ old('settings.to_email', $integration->settings['to_email'] ?? '') }}"
                                                placeholder="hr@your-company.com" required>
                                            @error('settings.to_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                @break
                                @endswitch
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-info" onclick="testConnection()">
                                <i class="fas fa-plug"></i> Тестировать соединение
                            </button>
                            <div>
                                <a href="{{ route('client.integrations.show', $integration) }}" class="btn btn-secondary me-2">
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
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Справка
                            </h5>
                        </div>
                        <div class="card-body">
                            @switch($integration->type)
                            @case('crm')
                            <h6>Настройка CRM интеграции</h6>
                            <ul class="small">
                                <li><strong>URL CRM</strong> - полный адрес API вашей CRM</li>
                                <li><strong>API ключ</strong> - токен для авторизации в CRM</li>
                                <li><strong>ID воронки</strong> - идентификатор воронки продаж</li>
                                <li><strong>ID этапа</strong> - этап, на который добавлять лиды</li>
                            </ul>
                            <div class="alert alert-warning alert-sm">
                                <strong>Совет:</strong> Обратитесь к документации вашей CRM для получения API ключа и идентификаторов.
                            </div>
                            @break

                            @case('telegram')
                            <h6>Настройка Telegram интеграции</h6>
                            <ol class="small">
                                <li>Создайте бота у @BotFather</li>
                                <li>Получите токен бота</li>
                                <li>Узнайте ID чата у @userinfobot</li>
                                <li>Добавьте бота в чат/канал</li>
                            </ol>
                            <div class="alert alert-info alert-sm">
                                <strong>Примечание:</strong> Бот должен иметь права на отправку сообщений в чат.
                            </div>
                            @break

                            @case('webhook')
                            <h6>Настройка Webhook интеграции</h6>
                            <ul class="small">
                                <li><strong>URL webhook</strong> - адрес для отправки данных</li>
                                <li><strong>HTTP метод</strong> - обычно POST</li>
                                <li><strong>Timeout</strong> - максимальное время ожидания</li>
                            </ul>
                            <div class="alert alert-info alert-sm">
                                <strong>Формат данных:</strong> JSON с полями name, email, phone, resume_file_url
                            </div>
                            @break

                            @case('email')
                            <h6>Настройка Email интеграции</h6>
                            <ul class="small">
                                <li><strong>SMTP хост</strong> - сервер исходящей почты</li>
                                <li><strong>SMTP порт</strong> - обычно 587 или 465</li>
                                <li><strong>Данные для авторизации</strong> на SMTP</li>
                                <li><strong>Email отправителя и получателя</strong></li>
                            </ul>
                            <div class="alert alert-warning alert-sm">
                                <strong>Gmail:</strong> Используйте App Password вместо обычного пароля.
                            </div>
                            @break
                            @endswitch
                        </div>
                    </div>

                    <!-- Подписка -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-crown"></i> Статус подписки
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(auth()->user()->isSubscriptionActive())
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle"></i>
                                <strong>Подписка активна</strong>
                            </div>
                            <p class="small text-muted mb-0">
                                До {{ auth()->user()->subscription_end_date ? auth()->user()->subscription_end_date->format('d.m.Y') : 'неизвестно' }}
                            </p>
                            @else
                            <div class="text-danger mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Подписка неактивна</strong>
                            </div>
                            <p class="small text-muted mb-0">
                                Интеграция не работает без активной подписки.
                            </p>
                            @endif
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

        fetch(`{{ route('client.integrations.test', $integration) }}`, {
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
</script>
@endpush