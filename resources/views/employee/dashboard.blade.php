@extends('layouts.app')

@section('title', 'Панель сотрудника')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-speedometer2"></i> Панель сотрудника</h1>
</div>

<!-- Предупреждение об истекшей подписке компании -->
@if(!auth('employee')->user()->client->isSubscriptionActive())
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Подписка компании истекла!</strong>
            Подписка вашей компании истекла {{ auth('employee')->user()->client->subscription_end_date->format('d.m.Y') }}.
            Функции могут быть ограничены. Обратитесь к администратору компании.
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

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Статус</div>
                        <div class="h5 mb-0 font-weight-bold">
                            @if(auth('employee')->user()->is_active)
                            Активен
                            @else
                            Неактивен
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-check fa-2x"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Токенов</div>
                        <div class="h5 mb-0 font-weight-bold">{{ auth('employee')->user()->tokens()->count() }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-shield-check fa-2x"></i>
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
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Клиент</div>
                        <div class="h5 mb-0 font-weight-bold">{{ auth('employee')->user()->client->name }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card text-white" style="background: linear-gradient(135deg, {{ auth('employee')->user()->client->isSubscriptionActive() ? '#28a745 0%, #20c997 100%' : '#dc3545 0%, #fd7e14 100%' }});">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Подписка компании</div>
                        <div class="h5 mb-0 font-weight-bold">
                            @if(auth('employee')->user()->client->isSubscriptionActive())
                            Активна
                            @else
                            Истекла
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-{{ auth('employee')->user()->client->isSubscriptionActive() ? 'calendar-check' : 'calendar-x' }} fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-person-lines-fill"></i> Мой профиль</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Имя:</label>
                            <p class="mb-0">{{ auth('employee')->user()->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email:</label>
                            <p class="mb-0">{{ auth('employee')->user()->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Должность:</label>
                            <p class="mb-0">{{ auth('employee')->user()->position ?? 'Не указана' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Телефон:</label>
                            <p class="mb-0">{{ auth('employee')->user()->phone ?? 'Не указан' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Статус:</label>
                            <p class="mb-0">
                                @if(auth('employee')->user()->is_active)
                                <span class="badge bg-success">Активен</span>
                                @else
                                <span class="badge bg-secondary">Неактивен</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Последний вход:</label>
                            <p class="mb-0">
                                @if(auth('employee')->user()->last_login_at)
                                {{ auth('employee')->user()->last_login_at->format('d.m.Y H:i') }}
                                @else
                                <span class="text-muted">Никогда</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    @if(auth('employee')->user()->client->isSubscriptionActive())
                    <a href="{{ route('employee.profile.edit') }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Редактировать профиль
                    </a>
                    @else
                    <button class="btn btn-secondary" disabled title="Подписка компании истекла">
                        <i class="bi bi-pencil"></i> Редактировать профиль
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-building"></i> Информация о клиенте</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Название:</label>
                    <p class="mb-0">{{ auth('employee')->user()->client->name }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Email:</label>
                    <p class="mb-0">{{ auth('employee')->user()->client->email }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Лимит сотрудников:</label>
                    <p class="mb-0">{{ auth('employee')->user()->client->max_employees }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Срок подписки:</label>
                    <p class="mb-0">
                        @if(auth('employee')->user()->client->subscription_expires_at)
                        до {{ auth('employee')->user()->client->subscription_expires_at->format('d.m.Y') }}
                        @if(auth('employee')->user()->client->subscription_expires_at->isPast())
                        <span class="badge bg-danger ms-1">Истекла</span>
                        @elseif(auth('employee')->user()->client->subscription_expires_at->diffInDays() <= 7)
                            <span class="badge bg-warning ms-1">Скоро истечет</span>
                            @else
                            <span class="badge bg-success ms-1">Активна</span>
                            @endif
                            @else
                            <span class="text-muted">Не указан</span>
                            @endif
                    </p>
                </div>
            </div>
        </div>

        @if(auth('employee')->user()->tokens()->count() > 0)
        <div class="card shadow">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-shield-check"></i> Мои токены</h6>
            </div>
            <div class="card-body">
                @if(!auth('employee')->user()->client->isSubscriptionActive())
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle"></i> Токены могут быть неактивны из-за истекшей подписки
                </div>
                @endif

                @foreach(auth('employee')->user()->tokens as $token)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <div class="fw-bold">{{ $token->name }}</div>
                        <small class="text-muted">Создан {{ $token->created_at->format('d.m.Y') }}</small>
                    </div>
                    @if(auth('employee')->user()->client->isSubscriptionActive())
                    <span class="badge bg-success">Активен</span>
                    @else
                    <span class="badge bg-danger">Заблокирован</span>
                    @endif
                </div>
                @if(!$loop->last)
                <hr>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="bi bi-clock-history"></i> Последняя активность</h6>
            </div>
            <div class="card-body">
                @php
                $recentActivities = auth('employee')->user()->activities()->latest()->take(5)->get();
                @endphp

                @if($recentActivities->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentActivities as $activity)
                    <div class="list-group-item px-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">
                                    @if($activity->description == 'created')
                                    Профиль создан
                                    @elseif($activity->description == 'updated')
                                    Профиль обновлен
                                    @else
                                    {{ $activity->description }}
                                    @endif
                                </div>
                                <small class="text-muted">{{ $activity->created_at->format('d.m.Y H:i') }}</small>
                            </div>
                            @if($activity->description == 'created')
                            <span class="badge bg-success">Создание</span>
                            @elseif($activity->description == 'updated')
                            <span class="badge bg-primary">Обновление</span>
                            @else
                            <span class="badge bg-secondary">Действие</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-muted mb-0">Активность не найдена</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection