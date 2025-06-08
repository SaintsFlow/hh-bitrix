@extends('layouts.app')

@section('title', 'Управление интеграциями')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Управление интеграциями</h1>
        <a href="{{ route('super-admin.integrations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать интеграцию
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="row">
        @foreach($clients as $client)
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-building"></i> {{ $client->name }}
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $client->isSubscriptionActive() ? 'bg-success' : 'bg-danger' }}">
                            {{ $client->isSubscriptionActive() ? 'Активная подписка' : 'Подписка истекла' }}
                        </span>
                        <a href="{{ route('super-admin.integrations.create', ['client_id' => $client->id]) }}"
                            class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-plus"></i> Добавить
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($client->integrationSettings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Тип</th>
                                    <th>Статус</th>
                                    <th>Последнее использование</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->integrationSettings as $integration)
                                <tr>
                                    <td>
                                        <strong>{{ $integration->name }}</strong>
                                    </td>
                                    <td>
                                        @switch($integration->type)
                                        @case('crm')
                                        <span class="badge bg-primary">CRM</span>
                                        @break
                                        @case('telegram')
                                        <span class="badge bg-info">Telegram</span>
                                        @break
                                        @case('webhook')
                                        <span class="badge bg-secondary">Webhook</span>
                                        @break
                                        @case('email')
                                        <span class="badge bg-warning">Email</span>
                                        @break
                                        @default
                                        <span class="badge bg-light text-dark">{{ $integration->type }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge {{ $integration->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $integration->is_active ? 'Активна' : 'Отключена' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($integration->last_used_at)
                                        {{ $integration->last_used_at->format('d.m.Y H:i') }}
                                        @else
                                        <span class="text-muted">Не использовалась</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('super-admin.integrations.show', $integration) }}"
                                                class="btn btn-outline-info"
                                                data-bs-toggle="tooltip"
                                                title="Просмотр">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('super-admin.integrations.edit', $integration) }}"
                                                class="btn btn-outline-warning"
                                                data-bs-toggle="tooltip"
                                                title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-outline-primary test-connection-btn"
                                                data-integration-id="{{ $integration->id }}"
                                                data-bs-toggle="tooltip"
                                                title="Тестировать соединение">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-outline-{{ $integration->is_active ? 'warning' : 'success' }} toggle-status-btn"
                                                data-integration-id="{{ $integration->id }}"
                                                data-bs-toggle="tooltip"
                                                title="{{ $integration->is_active ? 'Деактивировать' : 'Активировать' }}">
                                                <i class="bi bi-{{ $integration->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                            <form method="POST"
                                                action="{{ route('super-admin.integrations.destroy', $integration) }}"
                                                class="d-inline"
                                                onsubmit="return confirm('Вы уверены, что хотите удалить эту интеграцию?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-outline-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Удалить">
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
                    <div class="text-center py-4">
                        <i class="bi bi-plug display-1 text-muted"></i>
                        <p class="text-muted mt-2">У этого клиента пока нет настроенных интеграций</p>
                        <a href="{{ route('super-admin.integrations.create', ['client_id' => $client->id]) }}"
                            class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Создать первую интеграцию
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($clients->count() == 0)
    <div class="text-center py-5">
        <i class="bi bi-building display-1 text-muted"></i>
        <h4 class="text-muted mt-3">Клиенты не найдены</h4>
        <p class="text-muted">Сначала создайте клиентов, чтобы настроить для них интеграции</p>
        <a href="{{ route('super-admin.clients.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать клиента
        </a>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Test connection buttons
        document.querySelectorAll('.test-connection-btn').forEach(button => {
            button.addEventListener('click', function() {
                const integrationId = this.dataset.integrationId;
                const originalIcon = this.querySelector('i').className;
                const originalText = this.innerHTML;

                // Show loading state
                this.disabled = true;
                this.querySelector('i').className = 'bi bi-hourglass-split spin';

                fetch(`{{ route('super-admin.integrations.index') }}/${integrationId}/test`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-outline-success');
                            this.querySelector('i').className = 'bi bi-check-circle';
                            alert('Тест соединения прошел успешно!');
                        } else {
                            this.classList.remove('btn-outline-primary');
                            this.classList.add('btn-outline-danger');
                            this.querySelector('i').className = 'bi bi-x-circle';
                            alert('Ошибка соединения: ' + (data.error || data.message || 'Неизвестная ошибка'));
                        }

                        // Reset after 3 seconds
                        setTimeout(() => {
                            this.disabled = false;
                            this.className = 'btn btn-outline-primary test-connection-btn';
                            this.querySelector('i').className = originalIcon;
                        }, 3000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        this.disabled = false;
                        this.innerHTML = originalText;
                        alert('Произошла ошибка при тестировании соединения');
                    });
            });
        });

        // Toggle status buttons
        document.querySelectorAll('.toggle-status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const integrationId = this.dataset.integrationId;

                fetch(`{{ route('super-admin.integrations.index') }}/${integrationId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Произошла ошибка');
                    });
            });
        });
    });

    // CSS for spin animation
    const style = document.createElement('style');
    style.textContent = `
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
`;
    document.head.appendChild(style);
</script>
@endsection