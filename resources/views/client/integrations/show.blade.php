@extends('layouts.app')

@section('title', 'Просмотр интеграции')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">{{ $integration->name }}</h1>
                    <p class="text-muted mb-0">
                        <span class="badge bg-info me-2">{{ ucfirst($integration->type) }}</span>
                        <span class="badge bg-{{ $integration->is_active ? 'success' : 'danger' }}">
                            {{ $integration->is_active ? 'Активна' : 'Неактивна' }}
                        </span>
                    </p>
                </div>
                <div>
                    <a href="{{ route('client.integrations.edit', $integration) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <a href="{{ route('client.integrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к списку
                    </a>
                </div>
            </div>

            @if(!auth()->user()->isSubscriptionActive())
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Внимание!</strong> Ваша подписка неактивна. Эта интеграция не работает.
            </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Основная информация -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Основная информация
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Название:</dt>
                                        <dd class="col-sm-8">{{ $integration->name }}</dd>

                                        <dt class="col-sm-4">Тип:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-info">
                                                @switch($integration->type)
                                                @case('crm')
                                                CRM интеграция
                                                @break
                                                @case('telegram')
                                                Telegram уведомления
                                                @break
                                                @case('webhook')
                                                Webhook
                                                @break
                                                @case('email')
                                                Email уведомления
                                                @break
                                                @default
                                                {{ ucfirst($integration->type) }}
                                                @endswitch
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Статус:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-{{ $integration->is_active ? 'success' : 'danger' }}">
                                                {{ $integration->is_active ? 'Активна' : 'Неактивна' }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Создана:</dt>
                                        <dd class="col-sm-8">{{ $integration->created_at->format('d.m.Y H:i') }}</dd>

                                        <dt class="col-sm-4">Обновлена:</dt>
                                        <dd class="col-sm-8">{{ $integration->updated_at->format('d.m.Y H:i') }}</dd>
                                    </dl>
                                </div>
                            </div>

                            @if($integration->description)
                            <div class="row">
                                <div class="col-12">
                                    <hr>
                                    <h6>Описание:</h6>
                                    <p class="text-muted">{{ $integration->description }}</p>
                                </div>
                            </div>
                            @endif
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
                            @if($integration->settings && count($integration->settings) > 0)
                            @switch($integration->type)
                            @case('crm')
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-link"></i> Подключение</h6>
                                    <dl class="row">
                                        <dt class="col-sm-5">URL CRM:</dt>
                                        <dd class="col-sm-7">
                                            @if(isset($integration->settings['crm_url']))
                                            <code>{{ $integration->settings['crm_url'] }}</code>
                                            @else
                                            <span class="text-muted">Не задан</span>
                                            @endif
                                        </dd>
                                        <dt class="col-sm-5">API ключ:</dt>
                                        <dd class="col-sm-7">
                                            @if(isset($integration->settings['api_key']))
                                            <span class="text-muted">••••••••</span>
                                            <small class="text-muted">(скрыт)</small>
                                            @else
                                            <span class="text-muted">Не задан</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-funnel-dollar"></i> Воронка продаж</h6>
                                    <dl class="row">
                                        <dt class="col-sm-5">ID воронки:</dt>
                                        <dd class="col-sm-7">{{ $integration->settings['funnel_id'] ?? 'По умолчанию' }}</dd>
                                        <dt class="col-sm-5">ID этапа:</dt>
                                        <dd class="col-sm-7">{{ $integration->settings['stage_id'] ?? 'По умолчанию' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            @break

                            @case('telegram')
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fab fa-telegram"></i> Настройки Telegram</h6>
                                    <dl class="row">
                                        <dt class="col-sm-5">Токен бота:</dt>
                                        <dd class="col-sm-7">
                                            @if(isset($integration->settings['bot_token']))
                                            <span class="text-muted">••••••••</span>
                                            <small class="text-muted">(скрыт)</small>
                                            @else
                                            <span class="text-muted">Не задан</span>
                                            @endif
                                        </dd>
                                        <dt class="col-sm-5">ID чата:</dt>
                                        <dd class="col-sm-7">
                                            @if(isset($integration->settings['chat_id']))
                                            <code>{{ $integration->settings['chat_id'] }}</code>
                                            @else
                                            <span class="text-muted">Не задан</span>
                                            @endif
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                            @break

                            @case('webhook')
                            <div class="row">
                                <div class="col-md-8">
                                    <h6><i class="fas fa-link"></i> Настройки Webhook</h6>
                                    <dl class="row">
                                        <dt class="col-sm-3">URL:</dt>
                                        <dd class="col-sm-9">
                                            @if(isset($integration->settings['webhook_url']))
                                            <code>{{ $integration->settings['webhook_url'] }}</code>
                                            @else
                                            <span class="text-muted">Не задан</span>
                                            @endif
                                        </dd>
                                        <dt class="col-sm-3">Метод:</dt>
                                        <dd class="col-sm-9">
                                            <span class="badge bg-secondary">{{ $integration->settings['method'] ?? 'POST' }}</span>
                                        </dd>
                                        <dt class="col-sm-3">Timeout:</dt>
                                        <dd class="col-sm-9">{{ $integration->settings['timeout'] ?? 30 }} сек</dd>
                                    </dl>
                                </div>
                            </div>
                            @break

                            @case('email')
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-server"></i> SMTP настройки</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">Хост:</dt>
                                        <dd class="col-sm-8">{{ $integration->settings['smtp_host'] ?? 'Не задан' }}</dd>
                                        <dt class="col-sm-4">Порт:</dt>
                                        <dd class="col-sm-8">{{ $integration->settings['smtp_port'] ?? 'Не задан' }}</dd>
                                        <dt class="col-sm-4">Пользователь:</dt>
                                        <dd class="col-sm-8">{{ $integration->settings['smtp_username'] ?? 'Не задан' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-envelope"></i> Email настройки</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">От кого:</dt>
                                        <dd class="col-sm-8">{{ $integration->settings['from_email'] ?? 'Не задан' }}</dd>
                                        <dt class="col-sm-4">Кому:</dt>
                                        <dd class="col-sm-8">{{ $integration->settings['to_email'] ?? 'Не задан' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            @break
                            @endswitch
                            @else
                            <p class="text-muted mb-0">Настройки не заданы</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Действия -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-tools"></i> Действия
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="testConnection({{ $integration->id }})">
                                    <i class="fas fa-plug"></i> Тестировать соединение
                                </button>

                                <form method="POST" action="{{ route('client.integrations.toggle', $integration) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $integration->is_active ? 'warning' : 'success' }} w-100">
                                        <i class="fas fa-{{ $integration->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $integration->is_active ? 'Выключить' : 'Включить' }}
                                    </button>
                                </form>

                                <hr>

                                <form method="POST" action="{{ route('client.integrations.destroy', $integration) }}"
                                    onsubmit="return confirm('Вы уверены, что хотите удалить эту интеграцию? Это действие нельзя отменить.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-trash"></i> Удалить интеграцию
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar"></i> Статистика
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-0">{{ $apiStats['resume_submissions_today'] }}</h4>
                                            <small class="text-muted">Резюме сегодня</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0">{{ $apiStats['resume_submissions_total'] }}</h4>
                                        <small class="text-muted">Резюме всего</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h6 class="text-info mb-0">{{ $apiStats['today_api_requests'] }}</h6>
                                            <small class="text-muted">API запросов сегодня</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h6 class="text-secondary mb-0">{{ $apiStats['total_api_requests'] }}</h6>
                                        <small class="text-muted">API запросов всего</small>
                                    </div>
                                </div>
                                <hr>
                                <p class="text-muted small mb-0">
                                    Статистика использования API интеграции
                                </p>

                                @if(count($apiStats['api_by_type']) > 0)
                                <div class="mt-3">
                                    <h6 class="text-start">Активность по типам:</h6>
                                    <div class="text-start">
                                        @foreach($apiStats['api_by_type'] as $type => $count)
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small">{{ $type }}</span>
                                            <span class="badge bg-light text-dark">{{ $count }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
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

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Тестирование соединения</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-3">Тестирование соединения с интеграцией...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function testConnection(integrationId) {
        const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
        modal.show();

        fetch(`/client/integrations/${integrationId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                modal.hide();

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
                modal.hide();
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