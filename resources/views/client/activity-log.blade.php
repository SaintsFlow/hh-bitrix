@extends('layouts.app')

@section('title', 'Журнал активности')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-clock-history"></i> Журнал активности</h1>
</div>

<div class="card shadow">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="card-title mb-0">История действий</h6>
            </div>
            <div class="col-auto">
                <form class="d-flex" method="GET">
                    <select name="filter" class="form-select form-select-sm me-2">
                        <option value="">Все действия</option>
                        <option value="created" {{ request('filter') == 'created' ? 'selected' : '' }}>Создание</option>
                        <option value="updated" {{ request('filter') == 'updated' ? 'selected' : '' }}>Обновление</option>
                        <option value="deleted" {{ request('filter') == 'deleted' ? 'selected' : '' }}>Удаление</option>
                    </select>
                    <input type="search" name="search" class="form-control form-control-sm"
                        placeholder="Поиск..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary btn-sm ms-2" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($activities->count() > 0)
        <div class="list-group list-group-flush">
            @foreach($activities as $activity)
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-auto">
                        @if($activity->description == 'created')
                        <span class="badge bg-success">Создано</span>
                        @elseif($activity->description == 'updated')
                        <span class="badge bg-primary">Обновлено</span>
                        @elseif($activity->description == 'deleted')
                        <span class="badge bg-danger">Удалено</span>
                        @else
                        <span class="badge bg-secondary">{{ $activity->description }}</span>
                        @endif
                    </div>
                    <div class="col">
                        <div class="fw-bold">
                            {{ class_basename($activity->subject_type) }}
                            @if($activity->subject)
                            "{{ $activity->subject->name ?? $activity->subject->email ?? $activity->subject->id }}"
                            @endif
                        </div>
                        <div class="text-muted small">
                            @if($activity->causer)
                            Пользователь: {{ $activity->causer->name }}
                            @endif
                            <span class="mx-2">•</span>
                            {{ $activity->created_at->format('d.m.Y H:i:s') }}
                        </div>
                        @if($activity->properties && count($activity->properties) > 0)
                        <div class="mt-2">
                            <details>
                                <summary class="text-primary" style="cursor: pointer;">Подробности</summary>
                                <div class="mt-2 small">
                                    @if(isset($activity->properties['attributes']))
                                    <div class="mb-2">
                                        <strong>Новые значения:</strong>
                                        <pre class="bg-light p-2 mt-1" style="font-size: 0.8em;">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                    @if(isset($activity->properties['old']))
                                    <div>
                                        <strong>Старые значения:</strong>
                                        <pre class="bg-light p-2 mt-1" style="font-size: 0.8em;">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </div>
                                    @endif
                                </div>
                            </details>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-clock-history" style="font-size: 3rem; color: #dee2e6;"></i>
            <p class="text-muted mt-3">Записи журнала не найдены</p>
        </div>
        @endif
    </div>
    @if($activities->hasPages())
    <div class="card-footer">
        {{ $activities->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Статистика активности</h6>
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="h5 text-success">{{ $activities->where('description', 'created')->count() }}</div>
                        <small class="text-muted">Создано объектов</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h5 text-primary">{{ $activities->where('description', 'updated')->count() }}</div>
                        <small class="text-muted">Обновлено объектов</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h5 text-danger">{{ $activities->where('description', 'deleted')->count() }}</div>
                        <small class="text-muted">Удалено объектов</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h5 text-info">{{ $activities->count() }}</div>
                        <small class="text-muted">Всего записей</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection