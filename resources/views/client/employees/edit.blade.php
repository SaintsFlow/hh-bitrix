@extends('layouts.app')

@section('title', 'Редактировать сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-gear"></i> Редактировать сотрудника</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('client.employees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="card-title mb-0">Информация о сотруднике</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('client.employees.update', $employee->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', $employee->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', $employee->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Новый пароль (оставьте пустым, чтобы не менять)</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                    id="position" name="position" value="{{ old('position', $employee->position) }}">
                                @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                {{ old('is_active', $employee->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Активный сотрудник
                            </label>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('client.employees.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Отмена
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Информация о сотруднике</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Создан:</strong> {{ $employee->created_at->format('d.m.Y H:i') }}</p>
                        <p><strong>Последнее обновление:</strong> {{ $employee->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Последний вход:</strong>
                            @if($employee->last_login_at)
                            {{ $employee->last_login_at->format('d.m.Y H:i') }}
                            @else
                            <span class="text-muted">Никогда</span>
                            @endif
                        </p>
                        <p><strong>Токенов:</strong> {{ $employee->tokens()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($employee->tokens()->count() > 0)
<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Токены доступа</h6>
                <form method="POST" action="{{ route('client.employees.revoke-token', $employee->id) }}"
                    style="display: inline;"
                    onsubmit="return confirm('Отозвать все токены сотрудника?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-shield-x"></i> Отозвать все токены
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Создан</th>
                                <th>Последнее использование</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->tokens as $token)
                            <tr>
                                <td>{{ $token->name }}</td>
                                <td>{{ $token->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    @if($token->last_used_at)
                                    {{ $token->last_used_at->format('d.m.Y H:i') }}
                                    @else
                                    <span class="text-muted">Не использовался</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            У этого сотрудника нет активных токенов доступа.
            <form method="POST" action="{{ route('client.employees.issue-token', $employee->id) }}"
                style="display: inline;" class="ms-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-shield-check"></i> Выдать токен
                </button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection