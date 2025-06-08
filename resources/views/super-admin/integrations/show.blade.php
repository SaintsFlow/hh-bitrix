@extends('layouts.app')

@section('title', 'Просмотр интеграции')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Просмотр интеграции</h1>
                    <p class="text-muted mb-0">Детальная информация об интеграции</p>
                </div>
                <div>
                    <a href="{{ route('super-admin.integrations.edit', $integration) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit"></i> Редактировать
                    </a>
                    <a href="{{ route('super-admin.integrations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Назад к списку
                    </a>
                </div>
            </div>

            <!-- Основная информация -->
            <div class="row">
                <div class="col-lg-8">
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
                                                {{ ucfirst($integration->type) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Статус:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge bg-{{ $integration->is_active ? 'success' : 'danger' }}">
                                                {{ $integration->is_active ? 'Активна' : 'Неактивна' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Клиент:</dt>
                                        <dd class="col-sm-8">
                                            <a href="#" class="text-decoration-none">
                                                {{ $integration->client->name }}
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Создана:</dt>
                                        <dd class="col-sm-8">{{ $integration->created_at->format('d.m.Y H:i') }}</dd>

                                        <dt class="col-sm-4">Обновлена:</dt>
                                        <dd class="col-sm-8">{{ $integration->updated_at->format('d.m.Y H:i') }}</dd>

                                        @if($integration->description)
                                        <dt class="col-sm-4">Описание:</dt>
                                        <dd class="col-sm-8">{{ $integration->description }}</dd>
                                        @endif
                                    </dl>
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
                            @if($integration->settings && count($integration->settings) > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Параметр</th>
                                            <th>Значение</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($integration->settings as $key => $value)
                                        <tr>
                                            <td>
                                                <code>{{ $key }}</code>
                                            </td>
                                            <td>
                                                @if(in_array($key, ['api_key', 'token', 'secret', 'password']))
                                                <span class="text-muted">••••••••</span>
                                                <small class="text-muted">(скрыто для безопасности)</small>
                                                @elseif(is_array($value))
                                                <code>{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code>
                                                @elseif(is_bool($value))
                                                <span class="badge bg-{{ $value ? 'success' : 'danger' }}">
                                                    {{ $value ? 'Да' : 'Нет' }}
                                                </span>
                                                @else
                                                {{ $value }}
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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

                                <form method="POST" action="{{ route('super-admin.integrations.toggle', $integration) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-{{ $integration->is_active ? 'warning' : 'success' }} w-100">
                                        <i class="fas fa-{{ $integration->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $integration->is_active ? 'Деактивировать' : 'Активировать' }}
                                    </button>
                                </form>

                                <hr>

                                <form method="POST" action="{{ route('super-admin.integrations.destroy', $integration) }}"
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
                    <div class="card">
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
                                            <h4 class="text-primary mb-0">0</h4>
                                            <small class="text-muted">Сегодня</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success mb-0">0</h4>
                                        <small class="text-muted">Всего</small>
                                    </div>
                                </div>
                                <hr>
                                <p class="text-muted small mb-0">
                                    Количество обработанных резюме через эту интеграцию
                                </p>
                            </div>
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

        fetch(`/super-admin/integrations/${integrationId}/test`, {
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