@extends('layouts.app')

@section('title', 'Панель клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Панель управления</h1>
</div>

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
        <div class="card text-white" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
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
                        <i class="bi bi-calendar-check fa-2x"></i>
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