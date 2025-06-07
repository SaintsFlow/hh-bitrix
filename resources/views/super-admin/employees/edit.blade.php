@extends('layouts.app')

@section('title', 'Редактировать сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Редактировать сотрудника</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('super-admin.employees') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Данные сотрудника: {{ $employee->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="client_id" class="form-label">Клиент *</label>
                        <select class="form-select @error('client_id') is-invalid @enderror"
                            id="client_id"
                            name="client_id"
                            required>
                            @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $employee->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                                ({{ $client->employees->count() }}/{{ $client->max_employees }} сотрудников)
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Имя сотрудника *</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $employee->name) }}"
                                required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email', $employee->email) }}"
                                required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('super-admin.employees') }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Карточка с информацией о сотруднике -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Информация о сотруднике</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Статус:</strong>
                            @if($employee->is_active)
                            <span class="badge bg-success">Активен</span>
                            @else
                            <span class="badge bg-danger">Неактивен</span>
                            @endif
                        </p>
                        <p><strong>Дата создания:</strong> {{ $employee->created_at->format('d.m.Y H:i') }}</p>
                        <p><strong>Последнее обновление:</strong> {{ $employee->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Последний вход:</strong>
                            @if($employee->last_login_at)
                            {{ $employee->last_login_at->format('d.m.Y H:i') }}
                            @else
                            Никогда
                            @endif
                        </p>
                        <p><strong>Токены API:</strong>
                            @if($employee->tokens->count() > 0)
                            <span class="badge bg-success">{{ $employee->tokens->count() }}</span>
                            @else
                            <span class="badge bg-secondary">Нет</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-3">
                    <h6>Управление токеном:</h6>
                    <div class="btn-group" role="group">
                        <form method="POST" action="{{ route('super-admin.employees.token', $employee) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-key"></i> Выпустить новый токен
                            </button>
                        </form>

                        @if($employee->tokens->count() > 0)
                        <form method="POST" action="{{ route('super-admin.employees.revoke-token', $employee) }}" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Вы уверены?')">
                                <i class="bi bi-shield-x"></i> Отозвать токен
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection