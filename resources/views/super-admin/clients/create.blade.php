@extends('layouts.app')

@section('title', 'Добавить клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-plus-circle"></i> Добавить клиента</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('super-admin.clients') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Данные нового клиента</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.clients.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Название компании *</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
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
                                value="{{ old('email') }}"
                                required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Пароль *</label>
                            <input type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                required>
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                            <input type="password"
                                class="form-control"
                                id="password_confirmation"
                                name="password_confirmation"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subscription_start_date" class="form-label">Дата начала подписки *</label>
                            <input type="date"
                                class="form-control @error('subscription_start_date') is-invalid @enderror"
                                id="subscription_start_date"
                                name="subscription_start_date"
                                value="{{ old('subscription_start_date', now()->toDateString()) }}"
                                required>
                            @error('subscription_start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="subscription_end_date" class="form-label">Дата окончания подписки *</label>
                            <input type="date"
                                class="form-control @error('subscription_end_date') is-invalid @enderror"
                                id="subscription_end_date"
                                name="subscription_end_date"
                                value="{{ old('subscription_end_date', now()->addYear()->toDateString()) }}"
                                required>
                            @error('subscription_end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="max_employees" class="form-label">Максимальное количество сотрудников *</label>
                        <input type="number"
                            class="form-control @error('max_employees') is-invalid @enderror"
                            id="max_employees"
                            name="max_employees"
                            value="{{ old('max_employees', 10) }}"
                            min="1"
                            max="1000"
                            required>
                        @error('max_employees')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Количество сотрудников, которых может добавить данный клиент</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('super-admin.clients') }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Создать клиента
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('subscription_start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(startDate);
        endDate.setFullYear(endDate.getFullYear() + 1);

        document.getElementById('subscription_end_date').value = endDate.toISOString().split('T')[0];
        document.getElementById('subscription_end_date').min = this.value;
    });
</script>
@endsection