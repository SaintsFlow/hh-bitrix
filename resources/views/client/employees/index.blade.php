@extends('layouts.app')

@section('title', 'Управление сотрудниками')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> Управление сотрудниками</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if(!auth('client')->user()->isSubscriptionActive())
        <button class="btn btn-secondary" disabled title="Подписка истекла">
            <i class="bi bi-plus-circle"></i> Добавить сотрудника
        </button>
        @elseif(auth('client')->user()->employees()->count() < auth('client')->user()->max_employees)
            <a href="{{ route('client.employees.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить сотрудника
            </a>
            @else
            <span class="text-muted">Достигнут лимит сотрудников ({{ auth('client')->user()->max_employees }})</span>
            @endif
    </div>
</div>

<!-- Предупреждение об истекшей подписке -->
@if(!auth('client')->user()->isSubscriptionActive())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Подписка истекла!</strong> Функции управления сотрудниками ограничены.
    Обратитесь к администратору для продления подписки.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card shadow">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="card-title mb-0">Список сотрудников</h6>
            </div>
            <div class="col-auto">
                <form class="d-flex" method="GET">
                    <input type="search" name="search" class="form-control form-control-sm"
                        placeholder="Поиск..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary btn-sm ms-2" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($employees->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Должность</th>
                        <th>Статус</th>
                        <th>Последний вход</th>
                        <th>Создан</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <span class="badge rounded-pill bg-primary">
                                        {{ substr($employee->name, 0, 1) }}
                                    </span>
                                </div>
                                {{ $employee->name }}
                            </div>
                        </td>
                        <td>{{ $employee->email }}</td>
                        <td>{{ $employee->position ?? 'Не указана' }}</td>
                        <td>
                            @if($employee->is_active)
                            <span class="badge bg-success">Активен</span>
                            @else
                            <span class="badge bg-secondary">Неактивен</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->last_login_at)
                            {{ $employee->last_login_at->format('d.m.Y H:i') }}
                            @else
                            <span class="text-muted">Никогда</span>
                            @endif
                        </td>
                        <td>{{ $employee->created_at->format('d.m.Y') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                @if(auth('client')->user()->isSubscriptionActive())
                                <a href="{{ route('client.employees.edit', $employee->id) }}"
                                    class="btn btn-outline-primary"
                                    title="Редактировать"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($employee->tokens()->count() > 0)
                                <form method="POST" action="{{ route('client.employees.revoke-token', $employee->id) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Отозвать токены сотрудника?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-warning"
                                        title="Отозвать токены"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <i class="bi bi-shield-x"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('client.employees.issue-token', $employee->id) }}"
                                    style="display: inline;">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-outline-success"
                                        title="Выдать токен"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <i class="bi bi-shield-check"></i>
                                    </button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('client.employees.destroy', $employee->id) }}"
                                    style="display: inline;"
                                    onsubmit="return confirm('Удалить сотрудника?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-danger"
                                        title="Удалить"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-outline-secondary" disabled title="Подписка истекла">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-secondary" disabled title="Подписка истекла">
                                    <i class="bi bi-shield-check"></i>
                                </button>
                                <button class="btn btn-outline-secondary" disabled title="Подписка истекла">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-people" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">Сотрудники не найдены</p>
            @if(auth('client')->user()->employees()->count() < auth('client')->user()->max_employees)
                <a href="{{ route('client.employees.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Добавить первого сотрудника
                </a>
                @endif
        </div>
        @endif
    </div>
    @if($employees->hasPages())
    <div class="card-footer">
        {{ $employees->links() }}
    </div>
    @endif
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Статистика</h6>
                <div class="row text-center">
                    <div class="col">
                        <div class="h4 text-primary">{{ $employees->count() }}</div>
                        <small class="text-muted">Всего сотрудников</small>
                    </div>
                    <div class="col">
                        <div class="h4 text-success">{{ $employees->where('is_active', true)->count() }}</div>
                        <small class="text-muted">Активных</small>
                    </div>
                    <div class="col">
                        <div class="h4 text-info">{{ auth('client')->user()->max_employees - $employees->count() }}</div>
                        <small class="text-muted">Доступно мест</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Токены доступа</h6>
                <div class="row text-center">
                    <div class="col">
                        <div class="h4 text-warning">{{ $employees->sum(function($e) { return $e->tokens()->count(); }) }}</div>
                        <small class="text-muted">Активных токенов</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Инициализация tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection