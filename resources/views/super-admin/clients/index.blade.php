@extends('layouts.app')

@section('title', 'Управление клиентами')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-building"></i> Клиенты</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('super-admin.clients.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Добавить клиента
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">Список клиентов</h5>
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
        @if($clients->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Подписка</th>
                        <th>Сотрудники</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clients as $client)
                    <tr>
                        <td>{{ $client->id }}</td>
                        <td>
                            <strong>{{ $client->name }}</strong>
                        </td>
                        <td>{{ $client->email }}</td>
                        <td>
                            <small class="text-muted">
                                {{ $client->subscription_start_date->format('d.m.Y') }} -
                                {{ $client->subscription_end_date->format('d.m.Y') }}
                            </small>
                            @if($client->subscription_end_date < now())
                                <br><span class="badge bg-danger">Истекла</span>
                                @elseif($client->subscription_end_date < now()->addDays(7))
                                    <br><span class="badge bg-warning">Истекает скоро</span>
                                    @else
                                    <br><span class="badge bg-success">Активна</span>
                                    @endif
                        </td>
                        <td>
                            <span class="badge bg-info">
                                {{ $client->employees->count() }} / {{ $client->max_employees }}
                            </span>
                        </td>
                        <td>
                            @if($client->is_active)
                            <span class="badge bg-success">Активен</span>
                            @else
                            <span class="badge bg-danger">Неактивен</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('super-admin.clients.edit', $client) }}"
                                    class="btn btn-outline-primary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <button type="button" class="btn btn-outline-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#extendModal{{ $client->id }}"
                                    title="Продлить подписку">
                                    <i class="bi bi-calendar-plus"></i>
                                </button>

                                <form method="POST" action="{{ route('super-admin.clients.toggle', $client) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-outline-{{ $client->is_active ? 'danger' : 'success' }}"
                                        title="{{ $client->is_active ? 'Деактивировать' : 'Активировать' }}"
                                        onclick="return confirm('Вы уверены?')">
                                        <i class="bi bi-{{ $client->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Modal для продления подписки -->
                            <div class="modal fade" id="extendModal{{ $client->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('super-admin.clients.extend', $client) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Продлить подписку</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Текущий срок окончания</label>
                                                    <input type="text" class="form-control"
                                                        value="{{ $client->subscription_end_date->format('d.m.Y') }}"
                                                        disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="subscription_end_date" class="form-label">Новый срок окончания</label>
                                                    <input type="date"
                                                        class="form-control"
                                                        name="subscription_end_date"
                                                        min="{{ now()->toDateString() }}"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                                <button type="submit" class="btn btn-success">Продлить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-building display-1 text-muted"></i>
            <h5 class="mt-3">Клиенты не найдены</h5>
            <p class="text-muted">Добавьте первого клиента, чтобы начать работу</p>
            <a href="{{ route('super-admin.clients.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Добавить клиента
            </a>
        </div>
        @endif
    </div>

    @if($clients->hasPages())
    <div class="card-footer">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection