@extends('layouts.app')

@section('title', 'Панель супер-админа')

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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Всего клиентов</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $clientsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Активных клиентов</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $activeClientsCount }}</div>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Всего сотрудников</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $employeesCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Система</div>
                        <div class="h5 mb-0 font-weight-bold">Активна</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-server fa-2x"></i>
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
                    <a href="{{ route('super-admin.clients.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Добавить клиента
                    </a>
                    <a href="{{ route('super-admin.employees.create') }}" class="btn btn-success">
                        <i class="bi bi-person-plus"></i> Добавить сотрудника
                    </a>
                    <a href="{{ route('super-admin.activity-log') }}" class="btn btn-info">
                        <i class="bi bi-activity"></i> Просмотреть логи
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Системная информация</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <strong>Laravel:</strong> {{ app()->version() }}
                    </li>
                    <li class="mb-2">
                        <strong>PHP:</strong> {{ PHP_VERSION }}
                    </li>
                    <li class="mb-2">
                        <strong>Время сервера:</strong> {{ now()->format('d.m.Y H:i:s') }}
                    </li>
                    <li>
                        <strong>Статус:</strong>
                        <span class="badge bg-success">Работает</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection