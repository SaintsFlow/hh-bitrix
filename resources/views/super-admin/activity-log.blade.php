@extends('layouts.app')

@section('title', 'Лог активности')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="bi bi-activity"></i> Лог активности</h1>
</div>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">История действий</h5>
            </div>
            <div class="col-auto">
                <form method="GET" class="d-flex">
                    <select name="model" class="form-select me-2">
                        <option value="">Все модели</option>
                        <option value="App\Models\Client" {{ request('model') == 'App\Models\Client' ? 'selected' : '' }}>Клиенты</option>
                        <option value="App\Models\Employee" {{ request('model') == 'App\Models\Employee' ? 'selected' : '' }}>Сотрудники</option>
                        <option value="App\Models\User" {{ request('model') == 'App\Models\User' ? 'selected' : '' }}>Пользователи</option>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-funnel"></i> Фильтр
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($activities->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата/Время</th>
                        <th>Действие</th>
                        <th>Объект</th>
                        <th>Пользователь</th>
                        <th>Детали</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>
                            <small class="text-muted">
                                {{ $activity->created_at->format('d.m.Y H:i:s') }}
                            </small>
                        </td>
                        <td>
                            @if($activity->description)
                            <span class="badge bg-primary">{{ $activity->description }}</span>
                            @else
                            <span class="badge bg-secondary">{{ $activity->event ?? 'updated' }}</span>
                            @endif
                        </td>
                        <td>
                            @if($activity->subject)
                            <strong>{{ class_basename($activity->subject_type) }}</strong><br>
                            <small class="text-muted">
                                @if($activity->subject_type == 'App\Models\Client')
                                {{ $activity->subject->name ?? 'Удален' }}
                                @elseif($activity->subject_type == 'App\Models\Employee')
                                {{ $activity->subject->name ?? 'Удален' }}
                                @elseif($activity->subject_type == 'App\Models\User')
                                {{ $activity->subject->name ?? 'Удален' }}
                                @else
                                ID: {{ $activity->subject_id }}
                                @endif
                            </small>
                            @else
                            <span class="text-muted">Объект удален</span>
                            @endif
                        </td>
                        <td>
                            @if($activity->causer)
                            <strong>{{ $activity->causer->name }}</strong><br>
                            <small class="text-muted">{{ $activity->causer->email }}</small>
                            @else
                            <span class="text-muted">Система</span>
                            @endif
                        </td>
                        <td>
                            @if($activity->properties && $activity->properties->count() > 0)
                            <button class="btn btn-sm btn-outline-info"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#details{{ $activity->id }}">
                                <i class="bi bi-eye"></i>
                            </button>
                            <div class="collapse mt-2" id="details{{ $activity->id }}">
                                <div class="card card-body bg-light">
                                    <small>
                                        @if(isset($activity->properties['old']))
                                        <strong>Было:</strong><br>
                                        @foreach($activity->properties['old'] as $key => $value)
                                        {{ $key }}: {{ $value }}<br>
                                        @endforeach
                                        <br>
                                        @endif

                                        @if(isset($activity->properties['attributes']))
                                        <strong>Стало:</strong><br>
                                        @foreach($activity->properties['attributes'] as $key => $value)
                                        {{ $key }}: {{ $value }}<br>
                                        @endforeach
                                        @endif

                                        @if(isset($activity->properties['old_end_date']))
                                        <strong>Дополнительно:</strong><br>
                                        Старая дата окончания: {{ $activity->properties['old_end_date'] }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            @else
                            <span class="text-muted">Нет данных</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-activity display-1 text-muted"></i>
            <h5 class="mt-3">Логи не найдены</h5>
            <p class="text-muted">История действий пока пуста</p>
        </div>
        @endif
    </div>

    @if($activities->hasPages())
    <div class="card-footer">
        {{ $activities->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection