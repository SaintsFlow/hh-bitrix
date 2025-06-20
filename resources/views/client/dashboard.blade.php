@extends('layouts.app')

@section('title', 'Панель клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Панель управления</h1>
</div>

<!-- Предупреждение об истекшей подписке -->
@if(!$client->isSubscriptionActive())
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Подписка истекла!</strong>
            Ваша подписка истекла {{ $client->subscription_end_date->format('d.m.Y') }}.
            Большинство функций заблокировано. Обратитесь к администратору для продления подписки.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
@endif

<!-- Сообщение из middleware -->
@if(session('subscription_expired'))
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill"></i>
            {{ session('subscription_expired') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
</div>
@endif

<!-- Блок уведомлений -->
@if($notifications->count() > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bi bi-bell-fill"></i> Уведомления
                    <span class="badge bg-dark">{{ $notifications->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                @foreach($notifications as $notification)
                <div class="alert alert-{{ $notification->type === 'expired' ? 'danger' : 'warning' }} alert-dismissible fade show" role="alert">
                    <i class="bi bi-{{ $notification->type === 'expired' ? 'exclamation-triangle-fill' : 'info-circle-fill' }}"></i>
                    {{ $notification->message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="markAsRead({{ $notification->id }})"></button>
                </div>
                @endforeach

                @if($notifications->count() >= 5)
                <div class="text-center">
                    <a href="{{ route('client.notifications') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-list"></i> Показать все уведомления
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Сотрудников</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $employeesCount }} / {{ $client->max_employees }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Активных</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $activeEmployeesCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Доступно мест</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $client->max_employees - $employeesCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-plus-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, {{ $client->isSubscriptionActive() ? '#28a745 0%, #20c997 100%' : '#dc3545 0%, #fd7e14 100%' }});">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Подписка</div>
                        <div class="h5 mb-0 font-weight-bold">
                            @if($client->isSubscriptionActive())
                            Активна
                            @else
                            Истекла
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-{{ $client->isSubscriptionActive() ? 'calendar-check' : 'calendar-x' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-graph-up"></i> Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$client->isSubscriptionActive())
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-lock-fill"></i> Функции заблокированы из-за истекшей подписки
                    </div>
                    <button class="btn btn-secondary" disabled title="Подписка истекла">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </button>
                    <button class="btn btn-secondary" disabled title="Подписка истекла">
                        <i class="bi bi-people"></i> Управление сотрудниками
                    </button>
                    <button class="btn btn-secondary" disabled title="Подписка истекла">
                        <i class="bi bi-activity"></i> Просмотреть логи
                    </button>
                    @else
                    @if($client->canAddEmployee())
                    <a href="{{ route('client.employees.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </a>
                    @else
                    <button class="btn btn-secondary" disabled>
                        <i class="bi bi-person-plus"></i> Лимит сотрудников достигнут
                    </button>
                    @endif

                    <a href="{{ route('client.employees.index') }}" class="btn btn-success">
                        <i class="bi bi-people"></i> Управление сотрудниками
                    </a>

                    <a href="{{ route('client.activity-log') }}" class="btn btn-info">
                        <i class="bi bi-activity"></i> Просмотреть логи
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Информация о подписке</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>Компания:</strong> {{ $client->name }}
                    </li>
                    <li class="mb-2">
                        <strong>Email:</strong> {{ $client->email }}
                    </li>
                    <li class="mb-2">
                        <strong>Начало подписки:</strong> {{ $client->subscription_start_date->format('d.m.Y') }}
                    </li>
                    <li class="mb-2">
                        <strong>Окончание подписки:</strong> {{ $client->subscription_end_date->format('d.m.Y') }}
                    </li>
                    <li>
                        <strong>Статус:</strong>
                        @if($client->isSubscriptionActive())
                        <span class="badge bg-success">Активна</span>
                        @else
                        <span class="badge bg-danger">Истекла</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function markAsRead(notificationId) {
        fetch(`/client/notifications/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Уведомление отмечено как прочитанное');
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
    }
</script>
@endsection