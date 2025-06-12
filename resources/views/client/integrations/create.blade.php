@extends('layouts.app')

@section('title', 'Создание интеграции')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Создание новой интеграции</h1>
                    <p class="text-muted mb-0">Настройка интеграции для автоматической обработки резюме</p>
                </div>
                <div>
                    <a href="{{ route('client.integrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к списку
                    </a>
                </div>
            </div>

            @if(!auth()->user()->isSubscriptionActive())
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Внимание!</strong> Ваша подписка неактивна. Интеграции не будут работать до активации подписки.
            </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('client.integrations.store') }}">
                        @csrf

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
                                                id="name" name="name" value="{{ old('name') }}" required
                                                placeholder="Например: Отправка в Bitrix24">
                                            @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Укажите понятное название для этой интеграции</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Тип интеграции <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type') is-invalid @enderror"
                                                id="type" name="type" required onchange="updateSettingsFields()">
                                                <option value="">Выберите тип</option>
                                                <option value="bitrix" {{ old('type') == 'bitrix' ? 'selected' : '' }}>Bitrix24</option>
                                                <option value="crm" {{ old('type') == 'crm' ? 'selected' : '' }}>CRM стороннее</option>
                                                <option value="telegram" {{ old('type') == 'telegram' ? 'selected' : '' }}>Telegram уведомления</option>
                                                <option value="webhook" {{ old('type') == 'webhook' ? 'selected' : '' }}>Webhook</option>
                                                <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>Email уведомления</option>
                                            </select>
                                            @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Описание</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3"
                                        placeholder="Краткое описание назначения этой интеграции">{{ old('description') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                            id="is_active" name="is_active" value="1"
                                            {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Активировать интеграцию сразу после создания
                                        </label>
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
                                <div id="integration-settings">
                                    <!-- Placeholder when no type selected -->
                                    <div id="no-type-selected" class="text-center text-muted py-4">
                                        <i class="fas fa-arrow-up fa-2x mb-3"></i>
                                        <p>Выберите тип интеграции выше, чтобы увидеть настройки</p>
                                    </div>

                                    <!-- CRM Settings -->
                                    <div id="crm-settings" class="integration-type-settings" style="display: none">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Для настройки CRM интеграции вам потребуется API ключ и данные воронки от вашей CRM системы.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="crm_url" class="form-label">URL CRM <span class="text-danger">*</span></label>
                                                    <input type="url" class="form-control" id="crm_url" name="settings[crm_url]"
                                                        value="{{ old('settings.crm_url') }}"
                                                        placeholder="https://your-domain.bitrix24.ru">
                                                    <div class="form-text">Полный URL вашей CRM системы</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="api_key" class="form-label">API ключ <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="api_key" name="settings[api_key]"
                                                        value="{{ old('settings.api_key') }}"
                                                        placeholder="Введите API ключ">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="funnel_id" class="form-label">ID воронки</label>
                                                    <input type="text" class="form-control" id="funnel_id" name="settings[funnel_id]"
                                                        value="{{ old('settings.funnel_id') }}"
                                                        placeholder="Например: 1">
                                                    <div class="form-text">Оставьте пустым для воронки по умолчанию</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="stage_id" class="form-label">ID этапа</label>
                                                    <input type="text" class="form-control" id="stage_id" name="settings[stage_id]"
                                                        value="{{ old('settings.stage_id') }}"
                                                        placeholder="Например: NEW">
                                                    <div class="form-text">Этап, на который добавлять новые лиды</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Telegram Settings -->
                                    <div id="telegram-settings" class="integration-type-settings" style="display: none">
                                        <div class="alert alert-info">
                                            <i class="fab fa-telegram"></i>
                                            Для настройки Telegram уведомлений создайте бота через @BotFather и получите токен.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="bot_token" class="form-label">Токен бота <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="bot_token" name="settings[bot_token]"
                                                        value="{{ old('settings.bot_token') }}"
                                                        placeholder="123456789:ABCDefGhIJKLmnoPQRSTUVWXYZ">
                                                    <div class="form-text">Получите у @BotFather в Telegram</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="chat_id" class="form-label">ID чата <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="chat_id" name="settings[chat_id]"
                                                        value="{{ old('settings.chat_id') }}"
                                                        placeholder="-123456789 или @channel_name">
                                                    <div class="form-text">ID чата или канала для отправки уведомлений</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Webhook Settings -->
                                    <div id="webhook-settings" class="integration-type-settings" style="display: none">
                                        <div class="alert alert-info">
                                            <i class="fas fa-link"></i>
                                            Webhook позволяет отправлять данные резюме на ваш собственный API endpoint.
                                        </div>

                                        <div class="mb-3">
                                            <label for="webhook_url" class="form-label">URL webhook <span class="text-danger">*</span></label>
                                            <input type="url" class="form-control" id="webhook_url" name="settings[webhook_url]"
                                                value="{{ old('settings.webhook_url') }}"
                                                placeholder="https://your-domain.com/api/webhook">
                                            <div class="form-text">URL для получения POST запросов с данными резюме</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="method" class="form-label">HTTP метод</label>
                                                    <select class="form-select" id="method" name="settings[method]">
                                                        <option value="POST" {{ old('settings.method', 'POST') == 'POST' ? 'selected' : '' }}>POST</option>
                                                        <option value="PUT" {{ old('settings.method') == 'PUT' ? 'selected' : '' }}>PUT</option>
                                                        <option value="PATCH" {{ old('settings.method') == 'PATCH' ? 'selected' : '' }}>PATCH</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="timeout" class="form-label">Timeout (сек)</label>
                                                    <input type="number" class="form-control" id="timeout" name="settings[timeout]"
                                                        min="1" max="60" value="{{ old('settings.timeout', 30) }}">
                                                    <div class="form-text">Максимальное время ожидания ответа</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Email Settings -->
                                    <div id="email-settings" class="integration-type-settings" style="display: none">
                                        <div class="alert alert-info">
                                            <i class="fas fa-envelope"></i>
                                            Настройте отправку уведомлений о новых резюме на email.
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_host" class="form-label">SMTP хост <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="smtp_host" name="settings[smtp_host]"
                                                        value="{{ old('settings.smtp_host') }}"
                                                        placeholder="smtp.gmail.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_port" class="form-label">SMTP порт <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" id="smtp_port" name="settings[smtp_port]"
                                                        value="{{ old('settings.smtp_port', 587) }}"
                                                        placeholder="587">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_username" class="form-label">SMTP пользователь <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="smtp_username" name="settings[smtp_username]"
                                                        value="{{ old('settings.smtp_username') }}"
                                                        placeholder="your-email@gmail.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="smtp_password" class="form-label">SMTP пароль <span class="text-danger">*</span></label>
                                                    <input type="password" class="form-control" id="smtp_password" name="settings[smtp_password]"
                                                        value="{{ old('settings.smtp_password') }}"
                                                        placeholder="Пароль или app password">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="from_email" class="form-label">Email отправителя <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="from_email" name="settings[from_email]"
                                                        value="{{ old('settings.from_email') }}"
                                                        placeholder="noreply@your-domain.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="to_email" class="form-label">Email получателя <span class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="to_email" name="settings[to_email]"
                                                        value="{{ old('settings.to_email') }}"
                                                        placeholder="hr@your-company.com">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <!-- <button type="button" class="btn btn-outline-info" onclick="testConnection()" style="display: none" id="test-button">
                                <i class="fas fa-plug"></i> Тестировать соединение
                            </button> -->
                            <div class="ms-auto">
                                <a href="{{ route('client.integrations.index') }}" class="btn btn-secondary me-2">
                                    Отмена
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Создать интеграцию
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
                            <div id="help-content">
                                <div id="help-default" class="help-section">
                                    <h6>Как работают интеграции?</h6>
                                    <p class="small">
                                        Интеграции позволяют автоматически отправлять данные резюме в ваши системы сразу после их получения.
                                    </p>
                                    <p class="small">
                                        Выберите тип интеграции выше, чтобы увидеть подробные инструкции по настройке.
                                    </p>
                                </div>

                                <div id="help-crm" class="help-section" style="display: none">
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
                                </div>

                                <div id="help-telegram" class="help-section" style="display: none">
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
                                </div>

                                <div id="help-webhook" class="help-section" style="display: none">
                                    <h6>Настройка Webhook интеграции</h6>
                                    <ul class="small">
                                        <li><strong>URL webhook</strong> - адрес для отправки данных</li>
                                        <li><strong>HTTP метод</strong> - обычно POST</li>
                                        <li><strong>Timeout</strong> - максимальное время ожидания</li>
                                    </ul>
                                    <div class="alert alert-info alert-sm">
                                        <strong>Формат данных:</strong> JSON с полями name, email, phone, resume_file_url
                                    </div>
                                </div>

                                <div id="help-email" class="help-section" style="display: none">
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription status -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-crown"></i> Статус подписки
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(auth()->user()->isSubscriptionActive())
                            <div class="text-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Подписка активна</strong>
                            </div>
                            <p class="small text-muted mb-0">
                                До {{ auth()->user()->subscription_end_date ? auth()->user()->subscription_end_date->format('d.m.Y') : 'неизвестно' }}
                            </p>
                            @else
                            <div class="text-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <strong>Подписка неактивна</strong>
                            </div>
                            <p class="small text-muted mb-0">
                                Интеграции не будут работать без активной подписки.
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

        // Hide/show placeholder
        const placeholder = document.getElementById('no-type-selected');
        const testButton = document.getElementById('test-button');

        if (type) {
            placeholder.style.display = 'none';
            testButton.style.display = 'inline-block';

            // Show relevant sections
            const settingsSection = document.getElementById(`${type}-settings`);
            const helpSection = document.getElementById(`help-${type}`);

            if (settingsSection) settingsSection.style.display = 'block';
            if (helpSection) helpSection.style.display = 'block';
        } else {
            placeholder.style.display = 'block';
            testButton.style.display = 'none';
            document.getElementById('help-default').style.display = 'block';
        }
    }

    // Initialize form on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSettingsFields();
    });
</script>
@endpush