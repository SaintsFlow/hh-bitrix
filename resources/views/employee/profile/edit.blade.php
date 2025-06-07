@extends('layouts.app')

@section('title', 'Редактировать профиль')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-person-gear"></i> Редактировать профиль</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">
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
                <h6 class="card-title mb-0">Личные данные</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employee.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name', auth('employee')->user()->name) }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" value="{{ old('email', auth('employee')->user()->email) }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                    id="position" name="position" value="{{ old('position', auth('employee')->user()->position) }}">
                                @error('position')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" name="phone" value="{{ old('phone', auth('employee')->user()->phone) }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Смена пароля</h6>
                    <p class="text-muted small">Оставьте поля пустыми, если не хотите менять пароль</p>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Текущий пароль</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password">
                                @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Подтверждение нового пароля</label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">
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
                <h6 class="card-title mb-0">Информация об аккаунте</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Статус:</strong>
                            @if(auth('employee')->user()->is_active)
                            <span class="badge bg-success">Активен</span>
                            @else
                            <span class="badge bg-secondary">Неактивен</span>
                            @endif
                        </p>
                        <p><strong>Создан:</strong> {{ auth('employee')->user()->created_at->format('d.m.Y H:i') }}</p>
                        <p><strong>Последнее обновление:</strong> {{ auth('employee')->user()->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Клиент:</strong> {{ auth('employee')->user()->client->name }}</p>
                        <p><strong>Последний вход:</strong>
                            @if(auth('employee')->user()->last_login_at)
                            {{ auth('employee')->user()->last_login_at->format('d.m.Y H:i') }}
                            @else
                            <span class="text-muted">Никогда</span>
                            @endif
                        </p>
                        <p><strong>Токенов:</strong> {{ auth('employee')->user()->tokens()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth('employee')->user()->tokens()->count() > 0)
<div class="row mt-4">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Мои токены доступа</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Название</th>
                                <th>Создан</th>
                                <th>Последнее использование</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(auth('employee')->user()->tokens as $token)
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
                                <td>
                                    <span class="badge bg-success">Активен</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle"></i>
                    <strong>Информация:</strong>
                    Токены доступа выдаются и отзываются вашим клиентом. Вы не можете управлять ими самостоятельно.
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection