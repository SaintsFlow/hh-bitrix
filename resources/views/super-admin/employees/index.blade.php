@extends('layouts.app')

@section('title', 'Управление сотрудниками')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-people"></i> Сотрудники</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('super-admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Добавить сотрудника
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Список сотрудников</h5>
            </div>
            <div class="col-auto">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Поиск..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary">
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
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Клиент</th>
                        <th>Статус</th>
                        <th>Последний вход</th>
                        <th>Токен</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td>
                            <strong>{{ $employee->name }}</strong>
                        </td>
                        <td>{{ $employee->email }}</td>
                        <td>
                            <span class="badge bg-info">{{ $employee->client->name }}</span>
                        </td>
                        <td>
                            @if($employee->is_active)
                            <span class="badge bg-success">Активен</span>
                            @else
                            <span class="badge bg-danger">Неактивен</span>
                            @endif
                        </td>
                        <td>
                            @if($employee->last_login_at)
                            <small class="text-muted">
                                {{ $employee->last_login_at->format('d.m.Y H:i') }}
                            </small>
                            @else
                            <small class="text-muted">Никогда</small>
                            @endif
                        </td>
                        <td>
                            @if($employee->tokens->count() > 0)
                            <span class="badge bg-success">Есть</span>
                            @else
                            <span class="badge bg-secondary">Нет</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('super-admin.employees.edit', $employee) }}"
                                    class="btn btn-outline-primary"
                                    title="Редактировать"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="POST" action="{{ route('super-admin.employees.token', $employee) }}" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-outline-success"
                                        title="Выпустить/Обновить токен"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top">
                                        <i class="bi bi-key"></i>
                                    </button>
                                </form>

                                @if($employee->tokens->count() > 0)
                                <form method="POST" action="{{ route('super-admin.employees.revoke-token', $employee) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-danger"
                                        title="Отозвать токен"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        onclick="return confirm('Вы уверены?')">
                                        <i class="bi bi-shield-x"></i>
                                    </button>
                                </form>
                                @endif

                                <form method="POST" action="{{ route('super-admin.employees.destroy', $employee) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="btn btn-outline-danger"
                                        title="Удалить сотрудника"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        onclick="return confirm('Вы уверены? Это действие нельзя отменить!')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h5 class="mt-3">Сотрудники не найдены</h5>
            <p class="text-muted">Добавьте первого сотрудника, чтобы начать работу</p>
            <a href="{{ route('super-admin.employees.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Добавить сотрудника
            </a>
        </div>
        @endif
    </div>

    @if($employees->hasPages())
    <div class="card-footer">
        {{ $employees->links() }}
    </div>
    @endif
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