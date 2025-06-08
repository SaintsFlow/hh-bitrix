@extends('layouts.app')

@section('title', 'Мои интеграции')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Мои интеграции</h1>
        @if(auth('client')->user()->isSubscriptionActive())
        <a href="{{ route('client.integrations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Создать интеграцию
        </a>
        @endif
    </div>

    @if(!auth('client')->user()->isSubscriptionActive())
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>Подписка истекла!</strong>
        Ваша подписка истекла {{ auth('client')->user()->subscription_end_date->format('d.m.Y') }}.
        Функциональность ограничена. Обратитесь к администратору для продления подписки.
    </div>
    @endif

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

    @if($integrations->count() > 0)
    <div class="row">
        @foreach($integrations as $integration)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">{{ $integration->name }}</h6>
                    <div class="d-flex align-items-center gap-2">
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
                        <span class="badge {{ $integration->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $integration->is_active ? 'Активна' : 'Отключена' }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Создана:</small><br>
                        <strong>{{ $integration->created_at->format('d.m.Y H:i') }}</strong>
                    </div>

                    @if($integration->last_used_at)
                    <div class="mb-3">
                        <small class="text-muted">Последнее использование:</small><br>
                        <strong>{{ $integration->last_used_at->format('d.m.Y H:i') }}</strong>
                    </div>
                    @else
                    <div class="mb-3">
                        <small class="text-muted">Последнее использование:</small><br>
                        <span class="text-muted">Не использовалась</span>
                    </div>
                    @endif

                    <!-- Integration specific info -->
                    @switch($integration->type)
                    @case('crm')
                    @if(isset($integration->settings['api_url']))
                    <div class="mb-2">
                        <small class="text-muted">API URL:</small><br>
                        <code class="small">{{ Str::limit($integration->settings['api_url'], 40) }}</code>
                    </div>
                    @endif
                    @break
                    @case('telegram')
                    @if(isset($integration->settings['chat_id']))
                    <div class="mb-2">
                        <small class="text-muted">Chat ID:</small><br>
                        <code class="small">{{ $integration->settings['chat_id'] }}</code>
                    </div>
                    @endif
                    @break
                    @case('webhook')
                    @if(isset($integration->settings['webhook_url']))
                    <div class="mb-2">
                        <small class="text-muted">Webhook URL:</small><br>
                        <code class="small">{{ Str::limit($integration->settings['webhook_url'], 40) }}</code>
                    </div>
                    @endif
                    @break
                    @case('email')
                    @if(isset($integration->settings['recipients']))
                    <div class="mb-2">
                        <small class="text-muted">Получатели:</small><br>
                        <span class="small">{{ count($integration->settings['recipients']) }} адрес(ов)</span>
                    </div>
                    @endif
                    @break
                    @endswitch
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100">
                        <a href="{{ route('client.integrations.show', $integration) }}"
                            class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> Просмотр
                        </a>
                        @if(auth('client')->user()->isSubscriptionActive())
                        <a href="{{ route('client.integrations.edit', $integration) }}"
                            class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-pencil"></i> Изменить
                        </a>
                        <button type="button"
                            class="btn btn-outline-primary btn-sm test-connection-btn"
                            data-integration-id="{{ $integration->id }}">
                            <i class="bi bi-link-45deg"></i> Тест
                        </button>
                        @endif
                    </div>
                    @if(auth('client')->user()->isSubscriptionActive())
                    <div class="mt-2 d-flex gap-1">
                        <button type="button"
                            class="btn btn-outline-{{ $integration->is_active ? 'warning' : 'success' }} btn-sm flex-fill toggle-status-btn"
                            data-integration-id="{{ $integration->id }}">
                            <i class="bi bi-{{ $integration->is_active ? 'pause' : 'play' }}"></i>
                            {{ $integration->is_active ? 'Отключить' : 'Включить' }}
                        </button>
                        <form method="POST"
                            action="{{ route('client.integrations.destroy', $integration) }}"
                            class="d-inline"
                            onsubmit="return confirm('Вы уверены, что хотите удалить эту интеграцию?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="row justify-content-center">
        <div class="col-md-6 text-center py-5">
            <i class="bi bi-plug display-1 text-muted"></i>
            <h4 class="text-muted mt-3">Интеграции не настроены</h4>
            <p class="text-muted">
                Настройте интеграции, чтобы автоматически отправлять данные резюме в ваши системы
            </p>
            @if(auth('client')->user()->isSubscriptionActive())
            <a href="{{ route('client.integrations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Создать первую интеграцию
            </a>
            @else
            <p class="text-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Продлите подписку для создания интеграций
            </p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test connection buttons
        document.querySelectorAll('.test-connection-btn').forEach(button => {
            button.addEventListener('click', function() {
                const integrationId = this.dataset.integrationId;
                const originalIcon = this.querySelector('i').className;
                const originalText = this.innerHTML;

                // Show loading state
                this.disabled = true;
                this.querySelector('i').className = 'bi bi-hourglass-split spin';

                fetch(`{{ route('client.integrations.index') }}/${integrationId}/test`, {
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
                            this.className = 'btn btn-outline-primary btn-sm test-connection-btn';
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

                fetch(`{{ route('client.integrations.index') }}/${integrationId}/toggle`, {
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