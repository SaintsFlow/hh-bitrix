@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-bell"></i> Уведомления</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        @if($notifications->where('is_read', false)->count() > 0)
        <form action="{{ route('client.notifications.mark-all-read') }}" method="POST" class="me-2">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm" data-bs-toggle="tooltip" title="Отметить все как прочитанные">
                <i class="bi bi-check-all"></i> Отметить все как прочитанные
            </button>
        </form>
        @endif
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary btn-sm" data-bs-toggle="tooltip" title="Вернуться к панели">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>
</div>

<div class="row">
    <div class="col-12">
        @if($notifications->count() > 0)
        <div class="card">
            <div class="card-body p-0">
                @foreach($notifications as $notification)
                <div class="d-flex align-items-center p-3 border-bottom {{ $notification->is_read ? 'bg-light' : 'bg-white' }}">
                    <div class="flex-shrink-0 me-3">
                        <i class="bi bi-{{ $notification->type === 'expired' ? 'exclamation-triangle-fill text-danger' : 'info-circle-fill text-warning' }} fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 {{ $notification->is_read ? 'text-muted' : 'fw-bold' }}">
                                    {{ $notification->message }}
                                </p>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> {{ $notification->sent_at->format('d.m.Y H:i') }}
                                </small>
                            </div>
                            <div class="d-flex align-items-center">
                                @if(!$notification->is_read)
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="markAsRead({{ $notification->id }})" data-bs-toggle="tooltip" title="Отметить как прочитанное">
                                    <i class="bi bi-check"></i>
                                </button>
                                @else
                                <span class="badge bg-success me-2">Прочитано</span>
                                @endif
                                <span class="badge bg-{{ $notification->type === 'expired' ? 'danger' : 'warning' }}">
                                    {{ $notification->type === 'expired' ? 'Критично' : 'Предупреждение' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Пагинация -->
        <div class="d-flex justify-content-center mt-4">
            {{ $notifications->links() }}
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">Уведомлений нет</h4>
                <p class="text-muted">У вас пока нет никаких уведомлений.</p>
            </div>
        </div>
        @endif
    </div>
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

    function markAsRead(notificationId) {
        fetch(`/client/notifications/${notificationId}/mark-read`, {
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
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
    }
</script>
@endsection