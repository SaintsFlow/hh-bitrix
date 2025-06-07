@extends('layouts.app')

@section('title', 'Редактировать клиента')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-pencil"></i> Редактировать клиента</h1>
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
                <h5 class="card-title mb-0">Данные клиента: {{ $client->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.clients.update', $client) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Название компании *</label>
                            <input type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name', $client->name) }}"
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
                                value="{{ old('email', $client->email) }}"
                                required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subscription_start_date" class="form-label">Дата начала подписки *</label>
                            <input type="date"
                                class="form-control @error('subscription_start_date') is-invalid @enderror"
                                id="subscription_start_date"
                                name="subscription_start_date"
                                value="{{ old('subscription_start_date', $client->subscription_start_date->toDateString()) }}"
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
                                value="{{ old('subscription_end_date', $client->subscription_end_date->toDateString()) }}"
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
                            value="{{ old('max_employees', $client->max_employees) }}"
                            min="{{ $client->employees->count() }}"
                            max="1000"
                            required>
                        @error('max_employees')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Текущее количество сотрудников: {{ $client->employees->count() }}.
                            Нельзя установить лимит меньше текущего количества.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('super-admin.clients') }}" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Карточка со статистикой -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Статистика клиента</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3">
                            <h5 class="text-primary">{{ $client->employees->count() }}</h5>
                            <small class="text-muted">Всего сотрудников</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h5 class="text-success">{{ $client->employees->where('is_active', true)->count() }}</h5>
                            <small class="text-muted">Активных сотрудников</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h5 class="text-info">{{ $client->max_employees - $client->employees->count() }}</h5>
                            <small class="text-muted">Доступно мест</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3">
                            <h5 class="text-{{ $client->isSubscriptionActive() ? 'success' : 'danger' }}">
                                {{ $client->isSubscriptionActive() ? 'Активна' : 'Неактивна' }}
                            </h5>
                            <small class="text-muted">Подписка</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('subscription_start_date').addEventListener('change', function() {
        document.getElementById('subscription_end_date').min = this.value;
    });
</script>
@endsection